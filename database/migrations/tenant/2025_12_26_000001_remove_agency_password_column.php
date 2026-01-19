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
                if (Schema::hasColumn('agencies', 'password')) {
                    $table->dropColumn('password');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                if (! Schema::hasColumn('agencies', 'password')) {
                    $table->string('password')->nullable()->after('contact_person_designation');
                }
            });
        }
    }
};
