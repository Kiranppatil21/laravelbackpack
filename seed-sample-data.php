<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\Employee;
use App\Models\Agency;
use Illuminate\Support\Facades\DB;

echo "Creating sample data in central database...\n\n";

// Temporarily disable tenant scope for central DB seeding
DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// Create sample agencies
$agency1 = Agency::firstOrCreate(
    ['name' => 'Demo Security Agency'],
    [
        'address' => '123 Main St, Mumbai',
        'phone' => '9876543210',
        'email' => 'contact@demosecurity.com',
        'tenant_id' => 1
    ]
);
echo "✓ Created Agency: {$agency1->name}\n";

// Create sample clients
$clients = [
    ['name' => 'ABC Corporation', 'address' => 'Andheri, Mumbai', 'phone' => '9876543211', 'email' => 'abc@corp.com'],
    ['name' => 'XYZ Industries', 'address' => 'Bandra, Mumbai', 'phone' => '9876543212', 'email' => 'xyz@ind.com'],
    ['name' => 'Tech Park Ltd', 'address' => 'Powai, Mumbai', 'phone' => '9876543213', 'email' => 'info@techpark.com'],
];

$clientIds = [];
foreach ($clients as $clientData) {
    $client = Client::firstOrCreate(
        ['email' => $clientData['email']],
        array_merge($clientData, ['tenant_id' => 1, 'agency_id' => $agency1->id])
    );
    $clientIds[] = $client->id;
    echo "✓ Created Client: {$client->name}\n";
}

// Create sample employees with different designations
$designations = ['Security Guard', 'Supervisor', 'Manager', 'Officer', 'Executive', 'Watchman', 'Bouncer'];
$employeeCount = 0;

foreach ($designations as $index => $designation) {
    for ($i = 1; $i <= 3; $i++) {
        $clientId = $clientIds[array_rand($clientIds)];
        $employee = Employee::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '', $designation)) . $i . '@example.com'],
            [
                'tenant_id' => 1,
                'agency_id' => $agency1->id,
                'client_id' => $clientId,
                'first_name' => $designation,
                'last_name' => "Employee {$i}",
                'name' => "{$designation} Employee {$i}",
                'designation' => $designation,
                'phone' => '98765432' . str_pad($employeeCount, 2, '0', STR_PAD_LEFT),
                'job_role' => $designation,
                'monthly_salary' => rand(15000, 50000),
                'state' => 'Maharashtra',
                'kyc_status' => 'approved',
                'hired_at' => now()->subDays(rand(30, 365)),
            ]
        );
        $employeeCount++;
    }
}

echo "✓ Created {$employeeCount} Employees across all designations\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\n--- Summary ---\n";
echo "Agencies: " . Agency::count() . "\n";
echo "Clients: " . Client::count() . "\n";
echo "Employees: " . Employee::count() . "\n";

echo "\nSample data created successfully!\n";
echo "You can now access:\n";
echo "- Clients page: http://127.0.0.1:8001/admin/client\n";
echo "- Employees page: http://127.0.0.1:8001/admin/employee\n";
echo "- Bulk Attendance: http://127.0.0.1:8001/admin/bulk-attendance\n";
