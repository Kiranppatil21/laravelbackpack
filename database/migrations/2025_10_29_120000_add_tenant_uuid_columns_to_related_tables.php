<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'agencies',
            'clients',
            'employees',
            'attendance',
            'payrolls',
            'invoices',
            'tenant_subscriptions',
            'razorpay_payments',
            'domains',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (! Schema::hasColumn($table, 'tenant_uuid')) {
                    $t->string('tenant_uuid', 36)->nullable()->after('tenant_id');
                }
            });
        }

        // Backfill tenant_uuid values from central tenants table
        $tenants = DB::table('tenants')->select('id', 'uuid')->get();
        if ($tenants->isEmpty()) {
            return;
        }

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            foreach ($tenants as $tenant) {
                DB::table($table)
                    ->where('tenant_id', $tenant->id)
                    ->update(['tenant_uuid' => $tenant->uuid]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'agencies',
            'clients',
            'employees',
            'attendance',
            'payrolls',
            'invoices',
            'tenant_subscriptions',
            'razorpay_payments',
            'domains',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'tenant_uuid')) {
                    $t->dropColumn('tenant_uuid');
                }
            });
        }
    }
};
