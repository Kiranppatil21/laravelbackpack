<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Company, Designation, Client};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ClientFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_client_with_all_data()
    {
        // Create test data first
        $company = Company::create([
            'name' => 'Test Security Company',
            'email' => 'test@company.com',
            'phone' => '+91-9876543210',
            'address' => 'Test Address',
            'city' => 'Test City',
            'state' => 'Test State',
            'pincode' => '123456',
            'gst_no' => '27AABCU9603R1ZW',
            'pan_no' => 'AABCU9603R',
        ]);
        
        $designation = Designation::create([
            'name' => 'Security Manager',
            'description' => 'Manages security operations',
        ]);

        $clientData = [
            'company_id' => $company->id,
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'name_of_client' => 'Test Client Ltd',
            'to_title' => 'Mr.',
            'site_name' => 'Test Site',
            'address' => 'Test Address, City - 123456',
            'dob' => '1990-01-01',
            'contact_no_1' => '+91-9876543210',
            'password' => 'password123',
            'status' => 'active',
            'sms_reports' => true,
            'email_reports' => false,
            'contacts' => [
                [
                    'name' => 'John Doe',
                    'designation_id' => $designation->id,
                    'phone' => '+91-9876543211',
                    'email' => 'john@client.com'
                ],
                [
                    'name' => 'Jane Smith',
                    'designation_id' => $designation->id,
                    'phone' => '+91-9876543212',
                    'email' => 'jane@client.com'
                ]
            ],
            'taxes' => [
                [
                    'tax_type' => 'gst',
                    'percentage' => 18.00,
                    'status' => 'active'
                ],
                [
                    'tax_type' => 'tds',
                    'percentage' => 2.00,
                    'status' => 'active'
                ]
            ]
        ];

        // Create client using the model directly (simulating controller logic)
        $client = Client::create([
            'company_id' => $clientData['company_id'],
            'name' => $clientData['name'],
            'email' => $clientData['email'],
            'name_of_client' => $clientData['name_of_client'],
            'to_title' => $clientData['to_title'],
            'site_name' => $clientData['site_name'],
            'address' => $clientData['address'],
            'dob' => $clientData['dob'],
            'contact_no_1' => $clientData['contact_no_1'],
            'password' => Hash::make($clientData['password']),
            'status' => $clientData['status'],
            'sms_reports' => $clientData['sms_reports'],
            'email_reports' => $clientData['email_reports'],
        ]);

        // Create contacts
        foreach ($clientData['contacts'] as $contactData) {
            $client->contacts()->create($contactData);
        }

        // Create taxes
        foreach ($clientData['taxes'] as $taxData) {
            $client->taxes()->create($taxData);
        }

        // Verify client was created
        $this->assertDatabaseHas('clients', [
            'name_of_client' => 'Test Client Ltd',
            'email' => 'test@client.com',
            'company_id' => $company->id,
        ]);

        // Verify contacts were created
        $this->assertDatabaseHas('client_contacts', [
            'client_id' => $client->id,
            'name' => 'John Doe',
            'email' => 'john@client.com'
        ]);

        $this->assertDatabaseHas('client_contacts', [
            'client_id' => $client->id,
            'name' => 'Jane Smith',
            'email' => 'jane@client.com'
        ]);

        // Verify taxes were created
        $this->assertDatabaseHas('client_taxes', [
            'client_id' => $client->id,
            'tax_type' => 'gst',
            'percentage' => 18.00
        ]);

        $this->assertDatabaseHas('client_taxes', [
            'client_id' => $client->id,
            'tax_type' => 'tds',
            'percentage' => 2.00
        ]);

        // Test relationships
        $client = $client->fresh(['company', 'contacts', 'taxes']);
        $this->assertEquals('Test Security Company', $client->company->name);
        $this->assertCount(2, $client->contacts);
        $this->assertCount(2, $client->taxes);

        echo "\n✅ Client creation test passed!\n";
        echo "Client: {$client->name_of_client}\n";
        echo "Company: {$client->company->name}\n";
        echo "Contacts: {$client->contacts->count()}\n";
        echo "Tax Details: {$client->taxes->count()}\n";
    }

    /** @test */
    public function client_serial_number_generation_works()
    {
        // Test auto-incrementing serial numbers
        $firstSerial = Client::getNextSerialNumber();
        $this->assertEquals(1, $firstSerial);

        // Create a client
        Client::create([
            'name' => 'First Client',
            'email' => 'first@client.com',
            'name_of_client' => 'First Client Ltd',
            'password' => Hash::make('password123'),
        ]);

        $secondSerial = Client::getNextSerialNumber();
        $this->assertEquals(2, $secondSerial);

        echo "\n✅ Serial number generation test passed!\n";
        echo "First serial: {$firstSerial}\n";
        echo "Second serial: {$secondSerial}\n";
    }
}