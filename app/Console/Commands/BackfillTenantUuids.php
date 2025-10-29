<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTenantUuids extends Command
{
    protected $signature = 'tenant:backfill-uuids {--tables=clients,agencies,employees,invoices,domains} {--chunk=1000} {--dry-run}';

    protected $description = 'Backfill tenant_uuid columns for tenant-scoped tables in chunked batches.';

    public function handle()
    {
        $tables = explode(',', $this->option('tables'));
        $chunk = (int) $this->option('chunk');
        $dry = (bool) $this->option('dry-run');

        foreach ($tables as $table) {
            $table = trim($table);

            if (! \Illuminate\Support\Facades\Schema::hasTable($table) || ! \Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_uuid')) {
                $this->line("Skipping {$table}: table or tenant_uuid column missing");
                continue;
            }

            $this->info("Processing table: {$table}");

            $totalMissing = DB::table($table)->whereNull('tenant_uuid')->count();
            $this->line("  Total missing: {$totalMissing}");

            $processed = 0;

            while (true) {
                $ids = DB::table($table)
                    ->whereNull('tenant_uuid')
                    ->orderBy('id')
                    ->limit($chunk)
                    ->pluck('id');

                if ($ids->isEmpty()) {
                    break;
                }

                foreach ($ids as $id) {
                    $row = DB::table($table)->where('id', $id)->first(['tenant_id']);
                    if (! $row || ! $row->tenant_id) {
                        $this->line("    id={$id}: no tenant_id, skipping");
                        continue;
                    }

                    $uuid = DB::table('tenants')->where('id', $row->tenant_id)->value('uuid');
                    if (! $uuid) {
                        $this->line("    id={$id}: tenant id {$row->tenant_id} has no uuid, skipping");
                        continue;
                    }

                    if ($dry) {
                        $this->line("    [dry] would set id={$id} tenant_uuid={$uuid}");
                    } else {
                        DB::table($table)->where('id', $id)->update(['tenant_uuid' => $uuid]);
                        $this->line("    updated id={$id} -> {$uuid}");
                    }

                    $processed++;
                }

                // safety: break if we've processed a lot in this run (helps interactive canary)
                if ($processed >= 100000) {
                    $this->line('Processed 100k rows in this session, pausing to avoid long locks.');
                    break 2;
                }
            }

            $this->line("  Finished table {$table}. Processed: {$processed}");
        }

        $this->info('Backfill complete (or dry-run complete).');

        return 0;
    }
}
