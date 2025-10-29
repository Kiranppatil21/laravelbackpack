<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    protected $signature = 'app:create-tenant {name} {domain} {--id=}';

    protected $description = 'Create a tenant and its domain (runs tenant DB creation & migrations).';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $id = $this->option('id') ?: null;

        $this->info("Creating tenant: {$name} (id: ".($id ?? '<auto>').')');

        // Create tenant row in central tenants table with a UUID while keeping
        // the integer primary key intact for existing FK relationships.
        $uuid = (string) \Illuminate\Support\Str::uuid();

        if ($id) {
            // If caller provided an explicit integer id, insert with that id.
            \Illuminate\Support\Facades\DB::table('tenants')->insert([
                'id' => $id,
                'uuid' => $uuid,
                'name' => $name,
                'domain' => $domain,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $tenantIntId = $id;
        } else {
            $tenantIntId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
                'uuid' => $uuid,
                'name' => $name,
                'domain' => $domain,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Fetch a stancl Tenant model instance by uuid for further operations
        $tenant = Tenant::where('uuid', $uuid)->first();

        // Create domain record pointing to the central tenant's integer id for now
        Domain::create([
            'domain' => $domain,
            'tenant_id' => $tenantIntId,
        ]);

        $this->info("Tenant created with id {$tenant->getTenantKey()} and domain {$domain}.");
        $this->info('Tenancy jobs (database create & migrate) have been dispatched.');

        return 0;
    }
}
