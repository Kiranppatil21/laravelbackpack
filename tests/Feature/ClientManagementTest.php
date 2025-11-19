<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Tenant, Company, Designation, Client};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and activate a tenant manually
        $tenant = Tenant::create(['id' => 'test-tenant']);
        $tenant->domains()->create(['domain' => 'test.localhost']);
        
        $tenant->run(function () {
            $this->artisan('migrate:fresh', ['--database' => 'tenant']);
        });
        
        tenancy()->initialize($tenant);
    }

    /** @test */
    public function can_view_client_creation_form()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create test data
        $company = Company::factory()->create(['name' => 'Test Company']);
        $designation = Designation::factory()->create(['name' => 'Test Designation']);
        
        // Visit the create page
        $response = $this->get(route('clients.create'));
        
        $response->assertStatus(200)
                ->assertInertia(fn (Assert $page) => 
                    $page->component('Clients/Create')
                         ->has('companies')
                         ->has('designations')
                         ->has('nextSerialNo')
                         ->has('taxTypes')
                         ->has('taxStatuses')
                );
    }

    /** @test */
    public function can_create_client_with_contacts_and_taxes()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create test data
        $company = Company::factory()->create();
        $designation = Designation::factory()->create();
        
        $clientData = [
            'company_id' => $company->id,
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'name_of_client' => 'Test Client Ltd',
            'to_title' => 'Mr.',
            'site_name' => 'Test Site',
            'address' => 'Test Address',
            'contact_no_1' => '+91-9876543210',
            'password' => 'password123',
            'contacts' => [
                [
                    'name' => 'John Doe',
                    'designation_id' => $designation->id,
                    'phone' => '+91-9876543211',
                    'email' => 'john@client.com'
                ]
            ],
            'taxes' => [
                [
                    'tax_type' => 'gst',
                    'percentage' => 18.00,
                    'status' => 'active'
                ]
            ]
        ];

        $response = $this->post(route('clients.store'), $clientData);

        $response->assertRedirect();
        
        // Verify client was created
        $this->assertDatabaseHas('clients', [
            'name_of_client' => 'Test Client Ltd',
            'email' => 'test@client.com'
        ]);
        
        // Verify contact was created
        $this->assertDatabaseHas('client_contacts', [
            'name' => 'John Doe',
            'email' => 'john@client.com'
        ]);
        
        // Verify tax was created
        $this->assertDatabaseHas('client_taxes', [
            'tax_type' => 'gst',
            'percentage' => 18.00
        ]);
    }
}