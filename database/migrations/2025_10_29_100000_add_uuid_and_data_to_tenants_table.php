<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * We'll add a nullable `uuid` string and a nullable `data` json column.
     * Existing tenants will be backfilled with UUID v4 values. This is
     * intentionally non-destructive: the integer primary `id` remains.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'uuid')) {
                $table->string('uuid', 36)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('tenants', 'data')) {
                // json is supported by most adapters; store tenant metadata here
                $table->json('data')->nullable()->after('uuid');
            }
        });

        // Backfill UUIDs for existing rows.
        // We intentionally do this in PHP so it's portable across DB drivers.
        $tenants = DB::table('tenants')->select('id')->get();
        foreach ($tenants as $t) {
            // only set if empty
            DB::table('tenants')
                ->where('id', $t->id)
                ->whereNull('uuid')
                ->update(['uuid' => (string) Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('tenants', 'uuid')) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};
