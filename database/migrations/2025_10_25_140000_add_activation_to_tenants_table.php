<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivationToTenantsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('tenants', 'active')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->boolean('active')->default(false);
                $table->timestamp('activated_at')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tenants', 'active')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn(['active', 'activated_at']);
            });
        }
    }
}
