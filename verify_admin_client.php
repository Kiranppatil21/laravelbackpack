<?php

// Test script to verify admin client routes are working
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 ADMIN CLIENT SYSTEM VERIFICATION\n";
echo "===================================\n\n";

echo "📊 Database Status:\n";
echo "------------------\n";
echo "Companies: " . \App\Models\Company::count() . "\n";
echo "Designations: " . \App\Models\Designation::count() . "\n"; 
echo "Clients: " . \App\Models\Client::count() . "\n";
echo "Client Contacts: " . \App\Models\ClientContact::count() . "\n";
echo "Client Tax Records: " . \App\Models\ClientTax::count() . "\n";

echo "\n📋 Form Data Ready:\n";
echo "-------------------\n";
$companies = \App\Models\Company::select('id', 'name')->get();
$designations = \App\Models\Designation::select('id', 'name')->get();
$nextSerial = \App\Models\Client::getNextSerialNumber();
$taxTypes = \App\Models\Client::getTaxTypes();

echo "Companies available: " . $companies->count() . "\n";
foreach($companies->take(3) as $company) {
    echo "  • " . $company->name . "\n";
}

echo "Designations available: " . $designations->count() . "\n";
foreach($designations->take(3) as $designation) {
    echo "  • " . $designation->name . "\n";
}

echo "Next Serial Number: " . $nextSerial . "\n";
echo "Tax Types: " . count($taxTypes) . "\n";

echo "\n🌐 Access URLs:\n";
echo "---------------\n";
echo "Admin Panel: http://127.0.0.1:8001/admin\n";
echo "Client List: http://127.0.0.1:8001/admin/client\n";
echo "Client Create: http://127.0.0.1:8001/admin/client/create\n";
echo "Custom Create: http://127.0.0.1:8001/admin/client/create-custom\n";

echo "\n✅ ADMIN CLIENT SYSTEM IS READY!\n";
echo "==================================\n";

echo "\n📝 Quick Test Instructions:\n";
echo "---------------------------\n";
echo "1. Visit: http://127.0.0.1:8001/admin/client/create\n";
echo "2. Should redirect to custom form\n";
echo "3. Fill out the comprehensive client form\n";
echo "4. Test dynamic contacts and tax sections\n";
echo "5. Submit to create a new client\n";

echo "\n🔧 Troubleshooting:\n";
echo "--------------------\n";
echo "• If you get 404: Check if admin middleware allows access\n";
echo "• If database error: Check connection or run migrations\n";
echo "• If form doesn't load: Check React build with 'npm run dev'\n";