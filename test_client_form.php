<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Switch to tenant context
$tenant = \App\Models\Tenant::first();
$tenant->run(function () {
    echo "Testing Client Form Data...\n";
    
    // Test Companies
    echo "\nCompanies available:\n";
    foreach(\App\Models\Company::all() as $company) {
        echo "- {$company->name} (ID: {$company->id})\n";
    }
    
    // Test Designations
    echo "\nDesignations available:\n";
    foreach(\App\Models\Designation::all() as $designation) {
        echo "- {$designation->name} (ID: {$designation->id})\n";
    }
    
    // Test Tax Types
    echo "\nTax Types:\n";
    foreach(\App\Models\ClientTax::getTaxTypes() as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
    
    // Test Existing Clients
    echo "\nExisting Clients:\n";
    foreach(\App\Models\Client::with(['company', 'contacts', 'taxes'])->get() as $client) {
        echo "- {$client->name_of_client} ({$client->email})\n";
        echo "  Company: " . ($client->company ? $client->company->name : 'None') . "\n";
        echo "  Contacts: {$client->contacts->count()}\n";
        echo "  Tax Details: {$client->taxes->count()}\n";
    }
    
    // Test Next Serial Number
    echo "\nNext Serial Number: " . \App\Models\Client::getNextSerialNumber() . "\n";
    
    echo "\n✅ All data is ready for the client form!\n";
});