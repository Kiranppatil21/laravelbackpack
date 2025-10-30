<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataColumnToTenantsTable extends Migration
{
    public function up()
    {
        // Keep migration idempotent, but only add `data` when the app expects it.
        if (! Schema::hasColumn('tenants', 'data')) {
            Schema::table('tenants', function (Blueprint $table) {
                // Some stancl/tenancy code expects a `data` JSON column on tenants.
                $table->json('data')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tenants', 'data')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('data');
            });
        }
    }
}
