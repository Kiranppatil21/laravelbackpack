<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;

echo "Starting client create/update persistence test...\n";

// Create and save a client
$client = new Client();
$client->name = 'Test Persistence Client';
$client->billing_rate = 50000.00;
$client->salary_cost = 20000.00;
$client->esi_rate = 1.75;
$client->pf_rate = 12.00;
$client->licensing_cost = 1000.00;
$client->administrative_overhead = 500.00;

if ($client->save()) {
    echo "Client created with ID: {$client->id}\n";
} else {
    echo "Failed to create client.\n";
    exit(1);
}

// Reload from DB to ensure persistence
$reloaded = Client::find($client->id);
echo "Reloaded billing_rate: " . ($reloaded->billing_rate ?? 'NULL') . "\n";
echo "Reloaded salary_cost: " . ($reloaded->salary_cost ?? 'NULL') . "\n";
echo "Reloaded esi_rate: " . ($reloaded->esi_rate ?? 'NULL') . "\n";
echo "Computed gross_margin: " . $reloaded->gross_margin . "\n";

// Update the billing rate and save
$reloaded->billing_rate = 60000.00;
$reloaded->save();

$updated = Client::find($client->id);
echo "After update billing_rate: " . $updated->billing_rate . "\n";
echo "After update gross_margin: " . $updated->gross_margin . "\n";

// Clean up: delete test record to avoid polluting dev DB
try {
    $updated->delete();
    echo "Test client deleted.\n";
} catch (Exception $e) {
    echo "Warning: failed to delete test client: " . $e->getMessage() . "\n";
}

echo "Done.\n";
