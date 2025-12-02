<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'agency_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->unsignedBigInteger('agency_id')->nullable()->after('tenant_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'agency_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('agency_id');
            });
        }
    }
};
