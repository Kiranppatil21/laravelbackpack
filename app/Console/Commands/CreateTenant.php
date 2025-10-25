<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenant extends Command
{
    protected $signature = 'app:create-tenant {name} {domain} {--id=}';

    protected $description = 'Create a tenant and its domain (runs tenant DB creation & migrations).';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $id = $this->option('id') ?: null;

        $data = [
            'name' => $name,
        ];

        if ($id) {
            $data['id'] = $id;
        }

        $this->info("Creating tenant: {$name} (id: " . ($id ?? '<auto>') . ")");

        $tenant = Tenant::create($data);

        // Create domain record pointing to the tenant
        Domain::create([
            'domain' => $domain,
            'tenant_id' => $tenant->getTenantKey(),
        ]);

        $this->info("Tenant created with id {$tenant->getTenantKey()} and domain {$domain}.");
        $this->info('Tenancy jobs (database create & migrate) have been dispatched.');

        return 0;
    }
}
