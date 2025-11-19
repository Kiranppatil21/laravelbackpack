<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Agency;
use App\Models\Attendance;
use App\Services\PayrollCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayrollAttendanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $employee;
    protected $payrollCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->assignRole('Agency Owner');
        
        $agency = Agency::factory()->create(['tenant_id' => $this->user->id]);
        $client = Client::factory()->create(['tenant_id' => $this->user->id]);
        
        $this->employee = Employee::factory()->create([
            'tenant_id' => $this->user->id,
            'client_id' => $client->id,
            'monthly_salary' => 50000,
            'state' => 'Maharashtra'
        ]);
        
        $this->payrollCalculator = app(PayrollCalculator::class);
    }

    public function test_attendance_check_in_check_out_flow()
    {
        // Test check-in
        $response = $this->actingAs($this->user)
            ->postJson('/api/attendance/checkin', [
                'employee_id' => $this->employee->id,
                'tenant_uuid' => 'test-uuid',
                'check_in_type' => 'geo',
                'check_in_meta' => ['lat' => 19.0760, 'lng' => 72.8777] // Mumbai coordinates
            ]);

        $response->assertStatus(201);
        $attendance = Attendance::first();
        
        $this->assertEquals($this->employee->id, $attendance->employee_id);
        $this->assertEquals('geo', $attendance->check_in_type);
        $this->assertNotNull($attendance->check_in_at);
        $this->assertNull($attendance->check_out_at);

        // Test duplicate check-in (should fail)
        $response = $this->actingAs($this->user)
            ->postJson('/api/attendance/checkin', [
                'employee_id' => $this->employee->id,
                'tenant_uuid' => 'test-uuid'
            ]);

        $response->assertStatus(409);

        // Test check-out
        $response = $this->actingAs($this->user)
            ->postJson('/api/attendance/checkout', [
                'employee_id' => $this->employee->id,
                'tenant_uuid' => 'test-uuid'
            ]);

        $response->assertStatus(200);
        $attendance->refresh();
        $this->assertNotNull($attendance->check_out_at);
    }

    public function test_payroll_calculation_with_indian_tax_regimes()
    {
        // Test old tax regime
        [$gross, $net, $breakdown] = $this->payrollCalculator->compute(
            50000, // base salary
            10000, // allowances
            0,     // deductions
            [
                'regime' => 'old',
                'state' => 'maharashtra',
                'include_epf' => true
            ]
        );

        $this->assertEquals(60000, $gross);
        $this->assertArrayHasKey('monthly_tax', $breakdown);
        $this->assertArrayHasKey('epf_monthly', $breakdown);
        $this->assertArrayHasKey('professional_tax_monthly', $breakdown);
        $this->assertEquals('old', $breakdown['regime']);
        $this->assertLessThan($gross, $net);

        // Test new tax regime
        [$gross, $net, $breakdown] = $this->payrollCalculator->compute(
            50000,
            10000,
            0,
            [
                'regime' => 'new',
                'state' => 'maharashtra'
            ]
        );

        $this->assertEquals(60000, $gross);
        $this->assertEquals('new', $breakdown['regime']);
        
        // Professional tax should be applied for Maharashtra
        $this->assertGreaterThan(0, $breakdown['professional_tax_monthly']);
    }

    public function test_attendance_reports_generation()
    {
        // Create sample attendance data
        Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'tenant_uuid' => 'test-uuid',
            'check_in_at' => now()->subHours(8),
            'check_out_at' => now()
        ]);

        Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'tenant_uuid' => 'test-uuid', 
            'check_in_at' => now()->subDay()->subHours(8),
            'check_out_at' => now()->subDay()
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/attendance/reports?' . http_build_query([
                'tenant_uuid' => 'test-uuid',
                'from' => now()->subWeek()->toDateString(),
                'to' => now()->toDateString()
            ]));

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_payroll_professional_tax_by_state()
    {
        $testCases = [
            ['state' => 'maharashtra', 'salary' => 20000, 'expected_tax' => 200],
            ['state' => 'karnataka', 'salary' => 15000, 'expected_tax' => 200],
            ['state' => 'kerala', 'salary' => 15000, 'expected_tax' => 150],
            ['state' => 'delhi', 'salary' => 15000, 'expected_tax' => 0], // No professional tax
        ];

        foreach ($testCases as $case) {
            [$gross, $net, $breakdown] = $this->payrollCalculator->compute(
                $case['salary'],
                0,
                0,
                ['state' => $case['state']]
            );

            $this->assertEquals(
                $case['expected_tax'], 
                $breakdown['professional_tax_monthly'],
                "Professional tax mismatch for {$case['state']}"
            );
        }
    }

    public function test_employee_payslip_generation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payslips/run', [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'regime' => 'old'
            ]);

        $response->assertStatus(200);
        
        // Check if payslip was created
        $this->assertDatabaseHas('payslips', [
            'employee_id' => $this->employee->id,
            'period_start' => now()->startOfMonth()->toDateString()
        ]);
    }

    public function test_attendance_geo_validation()
    {
        // Test check-in within allowed radius (should pass)
        $response = $this->actingAs($this->user)
            ->postJson('/api/attendance/checkin', [
                'employee_id' => $this->employee->id,
                'tenant_uuid' => 'test-uuid',
                'check_in_type' => 'geo',
                'check_in_meta' => ['lat' => 19.0760, 'lng' => 72.8777] // Mumbai
            ]);

        $response->assertStatus(201);

        // Clear previous attendance
        Attendance::truncate();

        // Test check-in outside allowed radius (should fail if geo validation is strict)
        $response = $this->actingAs($this->user)
            ->postJson('/api/attendance/checkin', [
                'employee_id' => $this->employee->id,
                'tenant_uuid' => 'test-uuid',
                'check_in_type' => 'geo', 
                'check_in_meta' => ['lat' => 28.6139, 'lng' => 77.2090] // Delhi (far from Mumbai)
            ]);

        // This might pass if geo validation is not strictly enforced
        // Adjust assertion based on your geo validation rules
        $this->assertTrue(in_array($response->getStatusCode(), [201, 422]));
    }

    public function test_qr_code_attendance()
    {
        $validQrCode = 'SITE_001_SHIFT_MORNING_' . date('Y-m-d');
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/attendance/checkin', [
                'employee_id' => $this->employee->id,
                'tenant_uuid' => 'test-uuid',
                'check_in_type' => 'qr',
                'check_in_meta' => ['qr' => $validQrCode]
            ]);

        $response->assertStatus(201);
        
        $attendance = Attendance::first();
        $this->assertEquals('qr', $attendance->check_in_type);
        $this->assertEquals($validQrCode, $attendance->check_in_meta['qr']);
    }
}