<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\ClientDesignationRate;

echo "Creating Sample Designation Rates for Clients...\n\n";

$clients = Client::all();

if ($clients->isEmpty()) {
    echo "✗ No clients found! Run seed-sample-data.php first.\n";
    exit(1);
}

$designations = [
    'Security Guard' => ['client' => 800, 'agency' => 650, 'client_ot' => 120, 'agency_ot' => 100],
    'Supervisor' => ['client' => 1200, 'agency' => 1000, 'client_ot' => 180, 'agency_ot' => 150],
    'Manager' => ['client' => 1800, 'agency' => 1500, 'client_ot' => 270, 'agency_ot' => 225],
    'Officer' => ['client' => 1500, 'agency' => 1250, 'client_ot' => 225, 'agency_ot' => 190],
    'Executive' => ['client' => 2000, 'agency' => 1700, 'client_ot' => 300, 'agency_ot' => 255],
    'Watchman' => ['client' => 750, 'agency' => 600, 'client_ot' => 110, 'agency_ot' => 90],
    'Bouncer' => ['client' => 900, 'agency' => 750, 'client_ot' => 135, 'agency_ot' => 115],
];

$totalRatesCreated = 0;

foreach ($clients->take(5) as $client) {
    echo "Setting rates for: {$client->name}\n";
    
    foreach ($designations as $designation => $rates) {
        ClientDesignationRate::updateOrCreate(
            [
                'client_id' => $client->id,
                'designation' => $designation,
            ],
            [
                'client_rate_per_day' => $rates['client'],
                'agency_rate_per_day' => $rates['agency'],
                'client_ot_rate_per_hour' => $rates['client_ot'],
                'agency_ot_rate_per_hour' => $rates['agency_ot'],
            ]
        );
        $totalRatesCreated++;
    }
    
    echo "  ✓ Added {$designation} rates\n";
}

echo "\n✅ Created {$totalRatesCreated} designation rate entries!\n\n";

echo "Sample Rate Structure:\n";
echo "┌─────────────────┬────────────┬────────────┬────────────┬────────────┐\n";
echo "│ Designation     │ Client/Day │ Agency/Day │ Client OT  │ Agency OT  │\n";
echo "├─────────────────┼────────────┼────────────┼────────────┼────────────┤\n";

foreach ($designations as $designation => $rates) {
    printf("│ %-15s │ ₹%-9.2f │ ₹%-9.2f │ ₹%-9.2f │ ₹%-9.2f │\n",
        $designation,
        $rates['client'],
        $rates['agency'],
        $rates['client_ot'],
        $rates['agency_ot']
    );
}

echo "└─────────────────┴────────────┴────────────┴────────────┴────────────┘\n\n";

echo "Now when creating invoices:\n";
echo "1. Go to: http://127.0.0.1:8001/admin/client\n";
echo "2. Edit a client to see/modify designation rates\n";
echo "3. Create invoice: http://127.0.0.1:8001/admin/client-invoice/create\n";
echo "4. Select client and month, then click 'Fetch Attendance Data'\n";
echo "5. Rates will be automatically populated based on employee designations\n";
