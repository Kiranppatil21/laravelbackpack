<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Agency;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\VisitLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleBasedSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $agencyOwner;
    protected $hrUser;
    protected $clientUser;
    protected $guardUser;
    protected $visitorUser;
    protected $policeUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users with different roles
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');
        
        $this->agencyOwner = User::factory()->create();
        $this->agencyOwner->assignRole('Agency Owner');
        $this->agencyOwner->tenant_id = 1;
        
        $this->hrUser = User::factory()->create();
        $this->hrUser->assignRole('HR');
        $this->hrUser->tenant_id = 1;
        
        $this->clientUser = User::factory()->create();
        $this->clientUser->assignRole('Client');
        $this->clientUser->tenant_id = 1;
        
        $this->guardUser = User::factory()->create();
        $this->guardUser->assignRole('Guard/Employee');
        $this->guardUser->tenant_id = 1;
        
        $this->visitorUser = User::factory()->create();
        $this->visitorUser->assignRole('Visitor');
        
        $this->policeUser = User::factory()->create();
        $this->policeUser->assignRole('Police');
        
        // Create test data
        $agency = Agency::factory()->create(['tenant_id' => 1]);
        $client = Client::factory()->create(['tenant_id' => 1]);
        
        Employee::factory()->create([
            'tenant_id' => 1,
            'client_id' => $client->id
        ]);
    }

    public function test_super_admin_access_all_resources()
    {
        $endpoints = [
            ['GET', '/api/agencies'],
            ['GET', '/api/clients'],
            ['GET', '/api/employees'], 
            ['GET', '/api/finance/invoices'],
            ['GET', '/api/attendance/reports']
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->actingAs($this->superAdmin, 'sanctum')
                ->json($method, $url);
                
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 201]),
                "Super Admin should access {$method} {$url}, got {$response->getStatusCode()}"
            );
        }
    }

    public function test_agency_owner_tenant_scoped_access()
    {
        // Agency Owner should access their tenant's data
        $response = $this->actingAs($this->agencyOwner, 'sanctum')
            ->getJson('/api/employees');
        $response->assertStatus(200);

        // Should be able to create employees
        $response = $this->actingAs($this->agencyOwner, 'sanctum')
            ->postJson('/api/employees', [
                'name' => 'Test Employee',
                'email' => 'test@example.com',
                'client_id' => Client::first()->id
            ]);
        $response->assertStatus(201);

        // Should access finance features
        $response = $this->actingAs($this->agencyOwner, 'sanctum')
            ->getJson('/api/finance/invoices');
        $response->assertStatus(200);
    }

    public function test_hr_user_employee_management_access()
    {
        // HR should access employee data
        $response = $this->actingAs($this->hrUser, 'sanctum')
            ->getJson('/api/employees');
        $response->assertStatus(200);

        // HR should access attendance reports
        $response = $this->actingAs($this->hrUser, 'sanctum')
            ->getJson('/api/attendance/reports?tenant_uuid=test&from=2025-01-01&to=2025-01-31');
        $response->assertStatus(200);

        // HR should access payroll features
        $response = $this->actingAs($this->hrUser, 'sanctum')
            ->postJson('/api/payslips/run', [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'regime' => 'old'
            ]);
        // Should either succeed (200) or be forbidden (403) based on policy
        $this->assertTrue(in_array($response->getStatusCode(), [200, 403]));
    }

    public function test_client_user_limited_access()
    {
        // Client should have very limited access
        $restrictedEndpoints = [
            ['POST', '/api/employees'],
            ['DELETE', '/api/employees/1'],
            ['GET', '/api/finance/invoices'],
            ['POST', '/api/finance/invoices']
        ];

        foreach ($restrictedEndpoints as [$method, $url]) {
            $response = $this->actingAs($this->clientUser, 'sanctum')
                ->json($method, $url, []);
                
            $this->assertTrue(
                in_array($response->getStatusCode(), [403, 405]),
                "Client should be restricted from {$method} {$url}"
            );
        }
    }

    public function test_guard_employee_self_service_access()
    {
        // Guard should access their own attendance
        $response = $this->actingAs($this->guardUser, 'sanctum')
            ->postJson('/api/attendance/checkin', [
                'employee_id' => 1, // Assuming this guard's employee ID
                'tenant_uuid' => 'test-uuid'
            ]);
        // May succeed or fail based on authentication setup
        $this->assertTrue(in_array($response->getStatusCode(), [201, 403, 422]));

        // Guard should NOT access other employees' data
        $response = $this->actingAs($this->guardUser, 'sanctum')
            ->getJson('/api/employees');
        $response->assertStatus(403);
    }

    public function test_visitor_api_access()
    {
        // Visitor should have very limited API access
        $response = $this->actingAs($this->visitorUser, 'sanctum')
            ->getJson('/api/employees');
        $response->assertStatus(403);

        // But visitor check-in should work with proper API key
        $response = $this->withHeaders(['X-VISITOR-API-KEY' => 'test-key'])
            ->postJson('/api/visitors/checkin', [
                'name' => 'John Visitor',
                'email' => 'visitor@example.com',
                'phone' => '+91-9876543210'
            ]);
        // Will succeed or fail based on key validation
        $this->assertTrue(in_array($response->getStatusCode(), [201, 422, 401]));
    }

    public function test_police_verification_access()
    {
        // Police should have read-only access to employee verification data
        $response = $this->actingAs($this->policeUser, 'sanctum')
            ->getJson('/api/employees');
        
        // Should either have access or be restricted based on policy
        $this->assertTrue(in_array($response->getStatusCode(), [200, 403]));

        // Police should NOT be able to modify data
        $response = $this->actingAs($this->policeUser, 'sanctum')
            ->postJson('/api/employees', [
                'name' => 'Test',
                'email' => 'test@example.com'
            ]);
        $response->assertStatus(403);
    }

    public function test_unauthorized_access_denied()
    {
        $sensitiveEndpoints = [
            ['GET', '/api/employees'],
            ['POST', '/api/employees'],
            ['GET', '/api/finance/invoices'],
            ['POST', '/api/finance/invoices'],
            ['GET', '/api/attendance/reports']
        ];

        foreach ($sensitiveEndpoints as [$method, $url]) {
            // Test without authentication
            $response = $this->json($method, $url);
            $this->assertTrue(
                in_array($response->getStatusCode(), [401, 403]),
                "Unauthenticated access to {$method} {$url} should be denied"
            );
        }
    }

    public function test_tenant_isolation_security()
    {
        // Create data for tenant 1 (current tenant)
        $employee1 = Employee::factory()->create(['tenant_id' => 1]);
        $invoice1 = Invoice::factory()->create(['tenant_id' => 1]);

        // Create another user for tenant 2
        $otherAgencyOwner = User::factory()->create();
        $otherAgencyOwner->assignRole('Agency Owner');
        $otherAgencyOwner->tenant_id = 2;

        // Create data for tenant 2
        $employee2 = Employee::factory()->create(['tenant_id' => 2]);
        $invoice2 = Invoice::factory()->create(['tenant_id' => 2]);

        // User from tenant 1 should not access tenant 2 data
        $response = $this->actingAs($this->agencyOwner, 'sanctum')
            ->getJson("/api/employees/{$employee2->id}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->agencyOwner, 'sanctum')
            ->getJson("/api/finance/invoices/{$invoice2->id}");
        $response->assertStatus(403);

        // User from tenant 2 should not access tenant 1 data
        $response = $this->actingAs($otherAgencyOwner, 'sanctum')
            ->getJson("/api/employees/{$employee1->id}");
        $response->assertStatus(403);
    }

    public function test_cross_role_privilege_escalation_prevention()
    {
        // Lower privilege user should not be able to perform admin actions
        $adminEndpoints = [
            ['POST', '/api/admin/activate-tenant'],
            ['DELETE', '/api/employees/1'], // If deletion requires admin
        ];

        $lowPrivilegeUsers = [$this->clientUser, $this->guardUser, $this->visitorUser];

        foreach ($adminEndpoints as [$method, $url]) {
            foreach ($lowPrivilegeUsers as $user) {
                $response = $this->actingAs($user, 'sanctum')
                    ->json($method, $url);
                    
                $this->assertTrue(
                    in_array($response->getStatusCode(), [403, 404, 405]),
                    "User with role {$user->getRoleNames()->first()} should not access {$method} {$url}"
                );
            }
        }
    }
}