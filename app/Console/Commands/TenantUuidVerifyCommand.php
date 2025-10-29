<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantUuidVerifyCommand extends Command
{
    protected $signature = 'tenant:verify-uuids {--tables=clients,agencies,employees,invoices,domains}';

    protected $description = 'Run quick verification queries to check tenant_uuid population and basic invariants.';

    public function handle()
    {
        $tables = explode(',', $this->option('tables'));

        $this->info('Verification summary: counts of rows with NULL tenant_uuid');

        foreach ($tables as $table) {
            $table = trim($table);
            if (! \Illuminate\Support\Facades\Schema::hasTable($table) || ! \Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_uuid')) {
                $this->line(sprintf("- %s: SKIPPED (table or column missing)", $table));
                continue;
            }

            $count = DB::table($table)->whereNull('tenant_uuid')->count();
            $this->line(sprintf("- %s: %d rows missing tenant_uuid", $table, $count));
        }

        $this->info('Spot-check: sample rows for tenant_id = 1 (if present)');
        foreach ($tables as $table) {
            $table = trim($table);
            if (! \Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_id') || ! \Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_uuid')) {
                $this->line("  - {$table}: no tenant_id or tenant_uuid column, skipping samples");
                continue;
            }

            $samples = DB::table($table)
                ->where('tenant_id', 1)
                ->limit(5)
                ->get(['id', 'tenant_id', 'tenant_uuid']);

            if ($samples->isEmpty()) {
                $this->line("  - {$table}: no rows with tenant_id=1");
                continue;
            }

            $this->line("  - {$table} samples:");
            foreach ($samples as $s) {
                $this->line(sprintf('    id=%s tenant_id=%s tenant_uuid=%s', $s->id, $s->tenant_id, $s->tenant_uuid));
            }
        }

        $orphanClients = DB::table('clients')->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('tenants')->whereColumn('tenants.id', 'clients.tenant_id');
        })->count();

        $this->line('Orphan clients (tenant_id missing in tenants): ' . $orphanClients);

        $this->info('Done. Use --tables override to restrict checks.');

        return 0;
    }
}
