<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;

// Create instance without saving to DB (safe test for accessor)
$client = new Client();
$client->billing_rate = 50000.00;        // billing collected from client
$client->salary_cost = 20000.00;         // salary cost per guard
$client->esi_rate = 1.75;                // percentage
$client->pf_rate = 12.00;                // percentage
$client->licensing_cost = 1000.00;       // licensing per contract
$client->administrative_overhead = 500.00; // admin overhead

echo "Inputs:\n";
echo "billing_rate: {$client->billing_rate}\n";
echo "salary_cost: {$client->salary_cost}\n";
echo "esi_rate: {$client->esi_rate}\n";
echo "pf_rate: {$client->pf_rate}\n";
echo "licensing_cost: {$client->licensing_cost}\n";
echo "administrative_overhead: {$client->administrative_overhead}\n\n";

echo "Computed gross_margin: {$client->gross_margin}\n";

// Also show breakdown
$salary = (float) $client->salary_cost;
$esiAmount = $salary * ((float) $client->esi_rate / 100.0);
$pfAmount = $salary * ((float) $client->pf_rate / 100.0);
$totalCosts = $salary + $client->licensing_cost + $client->administrative_overhead + $esiAmount + $pfAmount;

echo "Breakdown:\n";
echo "esi_amount: " . number_format($esiAmount, 2) . "\n";
echo "pf_amount: " . number_format($pfAmount, 2) . "\n";
echo "total_costs: " . number_format($totalCosts, 2) . "\n";

echo "billing_rate - total_costs = " . number_format((float)$client->billing_rate - $totalCosts, 2) . "\n";
