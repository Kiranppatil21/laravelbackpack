<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTenantUuidIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'clients',
            'agencies',
            'employees',
            'invoices',
            'domains',
            'tenant_subscriptions',
            'razorpay_payments',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_uuid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                $indexName = "idx_{$table}_tenant_uuid";
                // Add index if it does not already exist.
                if (! \Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_uuid')) {
                    return;
                }

                // Some DBs may error if index already exists; wrap defensively.
                try {
                    $tableBlueprint->index('tenant_uuid', $indexName);
                } catch (\Throwable $e) {
                    // ignore; index may already exist or DB not support creation in current state
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'clients',
            'agencies',
            'employees',
            'invoices',
            'domains',
            'tenant_subscriptions',
            'razorpay_payments',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_uuid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                $indexName = "idx_{$table}_tenant_uuid";
                try {
                    $tableBlueprint->dropIndex($indexName);
                } catch (\Throwable $e) {
                    // ignore; index may not exist or DB may have different naming conventions
                }
            });
        }
    }
}
