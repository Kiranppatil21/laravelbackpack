<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\VisitLog;
use Illuminate\Support\Facades\Schema;

class RoleBasedAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Roles considered low-privilege for auth checks. If these roles get 2xx on
     * sensitive endpoints, we flag them as potential authorization failures.
     */
    protected array $lowPrivilegeRoles = ['Visitor', 'Client'];

    public function setUp(): void
    {
        parent::setUp();
        // If the users table isn't present (common when running a single test
        // against an in-memory sqlite DB), run migrations so factories can create models.
        if (! Schema::hasTable('users')) {
            $this->artisan('migrate');
        }

        $this->seed(\Database\Seeders\TestRolesSeeder::class);
    }


    public function test_role_based_authorization_matrix()
    {
        // Define endpoints to test (method, uri, optional body)
        $endpoints = [
            // Finance
            ['method' => 'get', 'uri' => '/api/finance/invoices'],
            ['method' => 'post', 'uri' => '/api/finance/invoices', 'body' => ['issued_date' => now()->toDateString(), 'items' => [['description' => 'S', 'qty' => 1, 'unit_price' => 1, 'tax_rate' => 0]]]],
            ['method' => 'get', 'uri' => '/api/finance/invoices/1'],
            ['method' => 'post', 'uri' => '/api/finance/invoices/1/payments', 'body' => ['amount' => 10.0]],
            ['method' => 'post', 'uri' => '/api/finance/reports/statutory', 'body' => ['type' => 'gst', 'period_start' => now()->subMonth()->toDateString(), 'period_end' => now()->toDateString()]],
            ['method' => 'post', 'uri' => '/api/finance/reports/statutory/download', 'body' => ['type' => 'gst', 'period_start' => now()->subMonth()->toDateString(), 'period_end' => now()->toDateString()]],
            ['method' => 'get', 'uri' => '/api/finance/reports/profitability', 'body' => ['period_start' => now()->subMonth()->toDateString(), 'period_end' => now()->toDateString()]],

            // Agencies / Clients
            ['method' => 'get', 'uri' => '/api/agencies'],
            ['method' => 'get', 'uri' => '/api/clients'],

            // Employees / payroll-adjacent
            ['method' => 'get', 'uri' => '/api/employees'],
            ['method' => 'get', 'uri' => '/api/employees/1'],
            ['method' => 'post', 'uri' => '/api/attendance/checkin', 'body' => ['employee_id' => 1]],
            ['method' => 'post', 'uri' => '/api/attendance/checkout', 'body' => ['employee_id' => 1]],
            ['method' => 'get', 'uri' => '/api/attendance/payslips/1/download'],
        ];

        $roles = ['Visitor', 'Client', 'HR', 'Agency', 'Super Admin'];

        $failures = [];

        foreach ($endpoints as $ep) {
            $method = $ep['method'];
            $uri = $ep['uri'];
            $body = $ep['body'] ?? [];

            // Unauthenticated access should not return 2xx for protected endpoints
            $unauthResp = $this->{$method}($uri, $body);
            $this->assertTrue(in_array($unauthResp->getStatusCode(), [401, 302, 404]), "Unauthenticated endpoint $uri expected 401/302/404 but got {$unauthResp->getStatusCode()}");

            foreach ($roles as $role) {
                $user = User::factory()->create();
                if ($role !== 'Visitor') {
                    $user->assignRole($role);
                }
                $this->actingAs($user);

                $resp = $this->{$method}($uri, $body);
                $status = $resp->getStatusCode();

                // If a low-privilege role receives a 2xx response, it's a potential auth hole.
                if (in_array($role, $this->lowPrivilegeRoles) && $status >= 200 && $status < 300) {
                    $failures[] = [
                        'timestamp' => now()->toIso8601String(),
                        'method' => strtoupper($method),
                        'uri' => $uri,
                        'role' => $role,
                        'status' => $status,
                    ];
                }

                // logout between iterations
                $this->refreshApplication();
            }
        }

        // Ensure output directory exists
        $outDir = base_path('tests/_output');
        if (! is_dir($outDir)) {
            mkdir($outDir, 0777, true);
        }

        $csvPath = $outDir.'/role_auth_failures_'.date('Ymd_His').'.csv';
        $fh = fopen($csvPath, 'w');
        fputcsv($fh, ['timestamp', 'method', 'uri', 'role', 'status']);
        foreach ($failures as $f) {
            fputcsv($fh, [$f['timestamp'], $f['method'], $f['uri'], $f['role'], $f['status']]);
        }
        fclose($fh);

        // Fail the test if we have any unexpected successful accesses
        $this->assertEmpty($failures, 'Found unexpected successful accesses by low-privilege roles; see '.$csvPath);
    }
    public function test_visitors_endpoints_enforce_agency_role()
    {
        // Create a visit log to checkout
        $visitor = \App\Models\Visitor::create(['name' => 'Test', 'email' => 't@example.test']);
        $log = VisitLog::create(['visitor_id' => $visitor->id, 'check_in_at' => now()]);

        $roles = ['Visitor', 'Client', 'HR', 'Agency', 'Super Admin'];

        foreach ($roles as $role) {
            $user = User::factory()->create();
            if ($role !== 'Visitor') {
                // assign role if exists
                $user->assignRole($role);
            }

            $this->actingAs($user);

            // visitor logs list
            $resLogs = $this->getJson('/api/visitors/logs');

            if ($role === 'Agency' || $role === 'Super Admin') {
                $resLogs->assertStatus(200);
            } else {
                $resLogs->assertStatus(403);
            }

            // checkout endpoint
            $resCheckout = $this->postJson('/api/visitors/'.$log->id.'/checkout');
            if ($role === 'Agency' || $role === 'Super Admin') {
                $resCheckout->assertStatus(200);
            } else {
                $resCheckout->assertStatus(403);
            }
        }
    }

    public function test_finance_endpoints_require_authenticated_user_but_allow_basic_users()
    {
        // Prepare payload for creating an invoice
        $payload = [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service', 'qty' => 1, 'unit_price' => 100, 'tax_rate' => 18],
            ],
        ];

        // Unauthenticated should be 401
        $this->postJson('/api/finance/invoices', $payload)->assertStatus(401);

        // Authenticated user without special role should be allowed to create invoices
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->postJson('/api/finance/invoices', $payload)->assertStatus(201);

        // Ad-hoc statutory CSV download requires auth; test that authenticated user gets CSV
        $resp = $this->postJson('/api/finance/reports/statutory/download', ['type' => 'gst', 'period_start' => now()->subMonth()->toDateString(), 'period_end' => now()->toDateString()]);
        $resp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resp->headers->get('Content-Type'));
    }
}
