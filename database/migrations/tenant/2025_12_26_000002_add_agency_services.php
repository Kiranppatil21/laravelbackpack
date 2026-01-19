<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                if (! Schema::hasColumn('agencies', 'services')) {
                    $table->text('services')->nullable()->after('company_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                if (Schema::hasColumn('agencies', 'services')) {
                    $table->dropColumn('services');
                }
            });
        }
    }
};
