<?php

use App\Models\Client;
use App\Models\Employee;
use App\Models\Scopes\TenantScope;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('employees:assign-clients {--force : Reassign even employees that already have a client} {--random : Randomly choose the client for each employee}', function () {
    $force = (bool) $this->option('force');
    $random = (bool) $this->option('random');

    $employees = Employee::withoutGlobalScope(TenantScope::class)
        ->orderBy('tenant_id')
        ->orderBy('id')
        ->get();

    if ($employees->isEmpty()) {
        $this->warn('No employees found to assign.');

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }

    $clientsByTenant = Client::withoutGlobalScope(TenantScope::class)
        ->orderBy('tenant_id')
        ->orderBy('id')
        ->get()
        ->groupBy(function ($client) {
            return $client->tenant_id ?? 'null';
        })
        ->map(function ($group) {
            return $group->values();
        });

    if ($clientsByTenant->isEmpty()) {
        $this->error('No clients available to assign employees to.');

        return \Symfony\Component\Console\Command\Command::FAILURE;
    }

    $pointers = [];
    $updated = 0;
    $skipped = 0;
    $missing = 0;

    foreach ($employees as $employee) {
        if (! $force && $employee->client_id) {
            $skipped++;
            continue;
        }

        $tenantKey = $employee->tenant_id ?? 'null';
        $tenantClients = $clientsByTenant->get($tenantKey);

        if (! $tenantClients || $tenantClients->isEmpty()) {
            $missing++;
            $this->warn("No client available for employee {$employee->id} under tenant {$tenantKey}.");
            continue;
        }

        if (! $random) {
            if (! isset($pointers[$tenantKey])) {
                $pointers[$tenantKey] = 0;
            }

            $clientIndex = $pointers[$tenantKey] % $tenantClients->count();
            $client = $tenantClients[$clientIndex];
            $pointers[$tenantKey] = $clientIndex + 1;
        } else {
            $client = $tenantClients[random_int(0, $tenantClients->count() - 1)];
        }

        $employee->client_id = $client->id;
        $employee->save();

        $updated++;
    }

    $this->info("Employees assigned: {$updated}");

    if ($skipped > 0) {
        $this->line("Skipped (already assigned): {$skipped}");
    }

    if ($missing > 0) {
        $this->warn("Employees without available clients: {$missing}");
    }

    return \Symfony\Component\Console\Command\Command::SUCCESS;
})->purpose('Assign every employee to an available client for attendance testing.');

Artisan::command('employees:list-assignments', function () {
    $employees = Employee::withoutGlobalScope(TenantScope::class)
        ->with(['client:id,name,tenant_id'])
        ->orderBy('tenant_id')
        ->orderBy('name')
        ->get(['id', 'name', 'designation', 'client_id', 'tenant_id']);

    if ($employees->isEmpty()) {
        $this->warn('No employees found.');

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }

    $rows = $employees->map(function ($employee) {
        return [
            'ID' => $employee->id,
            'Employee' => $employee->name ?? 'Employee '.$employee->id,
            'Designation' => $employee->designation ?? 'N/A',
            'Client (Site)' => optional($employee->client)->name ?? 'Unassigned',
            'Tenant' => $employee->tenant_id ?? '—',
        ];
    });

    $this->table(array_keys($rows->first()), $rows->toArray());

    return \Symfony\Component\Console\Command\Command::SUCCESS;
})->purpose('Show each employee with their assigned client/site.');

Artisan::command('employees:assign-designations', function () {
    $designations = [
        'Security Guard',
        'Supervisor',
        'Manager',
        'Officer',
        'Executive',
        'Watchman',
        'Bouncer',
    ];

    $employees = Employee::withoutGlobalScope(TenantScope::class)
        ->orderBy('tenant_id')
        ->orderBy('id')
        ->get();

    if ($employees->isEmpty()) {
        $this->warn('No employees found.');

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }

    $count = 0;
    $designationsCount = count($designations);

    foreach ($employees as $index => $employee) {
        $designation = $designations[$index % $designationsCount];
        $employee->designation = $designation;
        $employee->save();
        $count++;
    }

    $this->info("Designations assigned for {$count} employees.");

    return \Symfony\Component\Console\Command\Command::SUCCESS;
})->purpose('Assign every employee a designation usable in bulk attendance.');
