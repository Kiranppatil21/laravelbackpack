<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientTax;
use Illuminate\Support\Facades\Hash;

class ClientTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample companies if they don't exist
        $companies = [
            ['name' => 'SecureServe Security Services'],
            ['name' => 'Guardian Protection Agency'],
            ['name' => 'Elite Security Solutions'],
        ];

        foreach ($companies as $companyData) {
            Company::firstOrCreate(
                ['name' => $companyData['name']],
                $companyData
            );
        }

        // Create sample designations if they don't exist
        $designations = [
            ['name' => 'General Manager'],
            ['name' => 'Security Manager'],
            ['name' => 'Site Supervisor'],
            ['name' => 'Admin Officer'],
            ['name' => 'HR Manager'],
            ['name' => 'Finance Manager'],
            ['name' => 'Operations Head'],
        ];

        foreach ($designations as $designationData) {
            Designation::firstOrCreate(
                ['name' => $designationData['name']],
                $designationData
            );
        }

        // Get the first company and some designations for relationships
        $company = Company::first();
        $designations = Designation::limit(3)->get();

        // Create sample clients
        $sampleClients = [
            [
                'company_id' => $company?->id,
                'name' => 'Metro Shopping Mall', // Add the original name field
                'email' => 'security@metromall.com', // Add the original email field
                'name_of_client' => 'Metro Shopping Mall',
                'to_title' => 'Mr.',
                'site_name' => 'Metro Mall - Main Branch',
                'address' => '123 Commercial Street, Business District, Mumbai - 400001',
                'dob' => '1985-06-15',
                'date_of_anniversary' => '2020-03-10',
                'contact_no_1' => '+91-9876543210',
                'contact_no_2' => '+91-9876543211',
                'site_supervisor_contact' => '+91-9876543212',
                'site_admin_contact' => '+91-9876543213',
                'site_manager_contact' => '+91-9876543214',
                'gst_no' => '27AABCU9603R1ZW',
                'tds_percentage' => 2.00,
                'pan_no' => 'AABCU9603R',
                'primary_email_1' => 'security@metromall.com',
                'primary_email_2' => 'admin@metromall.com',
                'additional_charges' => 5000.00,
                'additional_charges_comment' => 'Monthly maintenance charge for CCTV monitoring',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'sms_reports' => true,
                'sms_attendance' => true,
                'sms_bill' => true,
                'email_reports' => true,
                'email_attendance' => false,
                'email_bill' => true,
                'email_bill_reminder' => true,
                'email_payment_receipt' => true,
            ],
            [
                'company_id' => $company?->id,
                'name' => 'Tech Park Solutions', // Add the original name field
                'email' => 'facilities@techpark.com', // Add the original email field
                'name_of_client' => 'Tech Park Solutions',
                'to_title' => 'Ms.',
                'site_name' => 'IT Park Complex',
                'address' => '456 Tech Hub, Electronic City, Bangalore - 560100',
                'dob' => '1990-12-08',
                'date_of_anniversary' => '2018-07-22',
                'contact_no_1' => '+91-9123456789',
                'contact_no_2' => '+91-9123456790',
                'site_supervisor_contact' => '+91-9123456791',
                'site_admin_contact' => '+91-9123456792',
                'site_manager_contact' => '+91-9123456793',
                'gst_no' => '29AABCT1234R1ZX',
                'tds_percentage' => 1.50,
                'pan_no' => 'AABCT1234R',
                'primary_email_1' => 'facilities@techpark.com',
                'primary_email_2' => 'operations@techpark.com',
                'additional_charges' => 3000.00,
                'additional_charges_comment' => 'Night shift premium charges',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'sms_reports' => false,
                'sms_attendance' => true,
                'sms_bill' => false,
                'email_reports' => true,
                'email_attendance' => true,
                'email_bill' => true,
                'email_bill_reminder' => false,
                'email_payment_receipt' => true,
            ],
            [
                'company_id' => $company?->id,
                'name' => 'Residential Heights', // Add the original name field
                'email' => 'management@greenvalley.com', // Add the original email field
                'name_of_client' => 'Residential Heights',
                'to_title' => 'Dr.',
                'site_name' => 'Green Valley Apartments',
                'address' => '789 Residential Zone, Sector 15, Noida - 201301',
                'dob' => '1975-03-25',
                'date_of_anniversary' => '2019-11-05',
                'contact_no_1' => '+91-9555666777',
                'contact_no_2' => '+91-9555666778',
                'site_supervisor_contact' => '+91-9555666779',
                'site_admin_contact' => '+91-9555666780',
                'site_manager_contact' => '+91-9555666781',
                'gst_no' => '09AABCR5678R1ZY',
                'tds_percentage' => 0.00,
                'pan_no' => 'AABCR5678R',
                'primary_email_1' => 'management@greenvalley.com',
                'primary_email_2' => 'security@greenvalley.com',
                'additional_charges' => 2500.00,
                'additional_charges_comment' => 'Maintenance of guest entry system',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'sms_reports' => true,
                'sms_attendance' => false,
                'sms_bill' => true,
                'email_reports' => false,
                'email_attendance' => true,
                'email_bill' => false,
                'email_bill_reminder' => true,
                'email_payment_receipt' => false,
            ],
        ];

        foreach ($sampleClients as $clientData) {
            $client = Client::firstOrCreate(
                ['primary_email_1' => $clientData['primary_email_1']],
                $clientData
            );

            // Create sample contacts for each client
            $contacts = [
                [
                    'name' => 'John Smith',
                    'contact_no' => '+91-' . rand(9000000000, 9999999999),
                    'designation_id' => $designations->random()?->id,
                    'email' => 'john.smith@' . strtolower(str_replace(' ', '', $client->name_of_client)) . '.com',
                    'send_sms' => true,
                    'send_email' => true,
                ],
                [
                    'name' => 'Sarah Johnson',
                    'contact_no' => '+91-' . rand(9000000000, 9999999999),
                    'designation_id' => $designations->random()?->id,
                    'email' => 'sarah.j@' . strtolower(str_replace(' ', '', $client->name_of_client)) . '.com',
                    'send_sms' => false,
                    'send_email' => true,
                ],
            ];

            foreach ($contacts as $contactData) {
                ClientContact::firstOrCreate(
                    [
                        'client_id' => $client->id,
                        'email' => $contactData['email']
                    ],
                    array_merge($contactData, ['client_id' => $client->id])
                );
            }

            // Create sample tax records for each client
            $taxes = [
                [
                    'tax_status' => 'applicable',
                    'tax_type' => 'GST',
                    'tax_percent' => 18.00,
                    'tax_number' => 'GST' . rand(100000, 999999),
                ],
                [
                    'tax_status' => 'active',
                    'tax_type' => 'CGST',
                    'tax_percent' => 9.00,
                    'tax_number' => 'CGST' . rand(100000, 999999),
                ],
                [
                    'tax_status' => 'active',
                    'tax_type' => 'SGST',
                    'tax_percent' => 9.00,
                    'tax_number' => 'SGST' . rand(100000, 999999),
                ],
            ];

            foreach ($taxes as $taxData) {
                ClientTax::firstOrCreate(
                    [
                        'client_id' => $client->id,
                        'tax_type' => $taxData['tax_type']
                    ],
                    array_merge($taxData, ['client_id' => $client->id])
                );
            }
        }

        $this->command->info('Client test data seeded successfully!');
        $this->command->info('Created ' . Client::count() . ' clients with contacts and tax details.');
        $this->command->info('Sample login credentials:');
        $this->command->info('Email: security@metromall.com | Password: password123');
        $this->command->info('Email: facilities@techpark.com | Password: password123');
        $this->command->info('Email: management@greenvalley.com | Password: password123');
    }
}