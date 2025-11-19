<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;

class ComprehensiveEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $agency;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test user with tenant
        $this->user = User::factory()->create();
        $this->user->assignRole('Agency Owner');
        
        $this->agency = Agency::factory()->create(['tenant_id' => $this->user->id]);
        $this->client = Client::factory()->create(['tenant_id' => $this->user->id]);
    }

    public function test_complete_employee_registration_flow()
    {
        Storage::fake('public');
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/employees', [
                'name' => 'John Doe',
                'father_name' => 'Robert Doe', 
                'designation' => 'Security Guard',
                'education' => 'High School',
                'nationality' => 'Indian',
                'current_address' => '123 Main Street, Mumbai',
                'permanent_address' => '456 Home Street, Pune',
                'email' => 'john.doe@example.com',
                'phone' => '+91-9876543210',
                'date_of_birth' => '1990-05-15',
                'client_id' => $this->client->id,
                'monthly_salary' => 25000,
                
                // Dynamic sections
                'identity_proofs' => [
                    [
                        'identity_proof_type' => 'aadhar_card',
                        'identity_proof_no' => '123456789012',
                        'image_file' => File::fake()->image('aadhar.jpg')
                    ],
                    [
                        'identity_proof_type' => 'pan_card', 
                        'identity_proof_no' => 'ABCDE1234F',
                        'image_file' => File::fake()->image('pan.jpg')
                    ]
                ],
                
                'family_members' => [
                    [
                        'name' => 'Jane Doe',
                        'relationship' => 'spouse',
                        'age' => 28,
                        'phone_no' => '+91-9876543211',
                        'is_nominee' => true
                    ],
                    [
                        'name' => 'Baby Doe',
                        'relationship' => 'son', 
                        'age' => 5,
                        'phone_no' => null,
                        'is_nominee' => false
                    ]
                ],
                
                'acquaintances' => [
                    [
                        'name' => 'Reference Person',
                        'relationship' => 'friend',
                        'phone' => '+91-9876543212',
                        'address' => '789 Reference Street'
                    ]
                ],
                
                'uniform_allocations' => [
                    [
                        'client_id' => $this->client->id,
                        'uniform_type' => 'Security Uniform',
                        'size' => 'L',
                        'quantity' => 2,
                        'issue_date' => '2025-01-01'
                    ]
                ]
            ]);

        $response->assertStatus(201);
        $employee = Employee::first();
        
        // Assert main employee data
        $this->assertEquals('John Doe', $employee->name);
        $this->assertEquals('john.doe@example.com', $employee->email);
        
        // Assert dynamic sections were created
        $this->assertCount(2, $employee->identityProofs);
        $this->assertCount(2, $employee->familyMembers);
        $this->assertCount(1, $employee->acquaintances);
        $this->assertCount(1, $employee->uniformAllocations);
        
        // Assert files were uploaded
        Storage::disk('public')->assertExists($employee->identityProofs->first()->image_path);
    }

    public function test_employee_kyc_document_validation()
    {
        Storage::fake('public');
        
        // Test valid file types
        $response = $this->actingAs($this->user)
            ->postJson('/api/employees', [
                'name' => 'Test Employee',
                'email' => 'test@example.com',
                'client_id' => $this->client->id,
                'aadhar' => File::fake()->image('aadhar.jpg', 800, 600)->size(2000), // 2MB
                'pan' => File::fake()->image('pan.png'),
                'police_verification' => File::fake()->create('verification.pdf', 3000) // 3MB PDF
            ]);
            
        $response->assertStatus(201);
        
        // Test invalid file types
        $response = $this->actingAs($this->user)
            ->postJson('/api/employees', [
                'name' => 'Test Employee 2',
                'email' => 'test2@example.com', 
                'client_id' => $this->client->id,
                'aadhar' => File::fake()->create('document.txt', 1000) // Invalid type
            ]);
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['aadhar']);
    }
    
    public function test_employee_tenant_isolation()
    {
        // Create employee for current user's tenant
        $employee = Employee::factory()->create([
            'tenant_id' => $this->user->id,
            'client_id' => $this->client->id
        ]);
        
        // Create another user with different tenant
        $otherUser = User::factory()->create();
        $otherUser->assignRole('Agency Owner');
        
        // Other user should not be able to access this employee
        $response = $this->actingAs($otherUser)
            ->getJson("/api/employees/{$employee->id}");
            
        $response->assertStatus(403);
        
        // Current user should be able to access
        $response = $this->actingAs($this->user)
            ->getJson("/api/employees/{$employee->id}");
            
        $response->assertStatus(200);
    }
    
    public function test_employee_search_and_filtering()
    {
        // Create employees with different properties
        Employee::factory()->create([
            'name' => 'John Guard',
            'designation' => 'Security Guard',
            'tenant_id' => $this->user->id
        ]);
        
        Employee::factory()->create([
            'name' => 'Jane Supervisor', 
            'designation' => 'Security Supervisor',
            'tenant_id' => $this->user->id
        ]);
        
        // Test search by name
        $response = $this->actingAs($this->user)
            ->getJson('/api/employees?search=John');
            
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        
        // Test filter by designation
        $response = $this->actingAs($this->user)
            ->getJson('/api/employees?designation=Security Guard');
            
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}