<?php
// tools/create_test_employee.php
// usage: php tools/create_test_employee.php <user_email> <client_email> <employee_email>
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Usage:
//  php tools/create_test_employee.php <user_email> [client_email] [employee_email]
//  php tools/create_test_employee.php cleanup <employee_id> <client_id> [user_email]

$cmd = $argv[1] ?? null;

if ($cmd === 'cleanup') {
    $employeeId = $argv[2] ?? null;
    $clientId = $argv[3] ?? null;
    $userEmailToDelete = $argv[4] ?? null;

    $result = [];

    if ($employeeId) {
        $emp = \App\Models\Employee::find($employeeId);
        if ($emp) {
            $emp->delete();
            $result['deleted_employee'] = $employeeId;
        } else {
            $result['deleted_employee'] = null;
        }
    }

    if ($clientId) {
        $cli = \App\Models\Client::find($clientId);
        if ($cli) {
            $cli->delete();
            $result['deleted_client'] = $clientId;
        } else {
            $result['deleted_client'] = null;
        }
    }

    if ($userEmailToDelete) {
        $u = \App\Models\User::where('email', $userEmailToDelete)->first();
        if ($u) {
            $u->delete();
            $result['deleted_user'] = $userEmailToDelete;
        } else {
            $result['deleted_user'] = null;
        }
    }

    echo json_encode($result);
    exit(0);
}

$userEmail = $argv[1] ?? null;
$clientEmail = $argv[2] ?? 'client+'.time().'@example.com';
$employeeEmail = $argv[3] ?? 'emp+'.time().'@example.com';

if (! $userEmail) {
    echo json_encode(['error' => 'user_email required']);
    exit(1);
}

$user = \App\Models\User::where('email', $userEmail)->first();
// create client with tenant_id matching user if present, else null
$tenantId = $user?->tenant_id ?? null;
$client = \App\Models\Client::create([
    'name' => 'Cypress Client',
    'email' => $clientEmail,
    'tenant_id' => $tenantId,
]);

$employee = \App\Models\Employee::create([
    'first_name' => 'Cypress',
    'last_name' => 'Employee',
    'email' => $employeeEmail,
    'client_id' => $client->id,
    'tenant_id' => $tenantId,
]);

echo json_encode(['client_id' => $client->id, 'employee_id' => $employee->id]);
