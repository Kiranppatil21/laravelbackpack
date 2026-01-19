<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('agencies', 'is_active')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('communication_address');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('agencies', 'is_active')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
