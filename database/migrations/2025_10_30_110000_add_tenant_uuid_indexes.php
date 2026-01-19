<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds non-unique indexes on tenant_uuid for tenant-scoped tables.
     * We wrap each operation in try/catch so the migration is idempotent
     * and safe to run in environments where the index may already exist.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['clients', 'agencies', 'employees', 'invoices', 'domains'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_uuid')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    // create a predictable index name to make dropping straightforward
                    $indexName = $table . '_tenant_uuid_index';
                    // Avoid duplicate index creation by wrapping in try/catch
                    $t->index('tenant_uuid', $indexName);
                });
            } catch (\Exception $e) {
                // ignore - index may already exist or be created by another process
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = ['clients', 'agencies', 'employees', 'invoices', 'domains'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_uuid')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $indexName = $table . '_tenant_uuid_index';
                    // dropIndex accepts either the index name or the column array; we try both
                    try {
                        $t->dropIndex($indexName);
                    } catch (\Exception $e) {
                        try {
                            $t->dropIndex(['tenant_uuid']);
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                });
            } catch (\Exception $e) {
                // ignore
            }
        }
    }
};
