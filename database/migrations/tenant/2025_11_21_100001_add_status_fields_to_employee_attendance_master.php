<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employee_attendance_master')) {
            Schema::table('employee_attendance_master', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_attendance_master', 'status')) {
                    $table->string('status', 20)->default('draft')->after('user_type');
                }
                if (!Schema::hasColumn('employee_attendance_master', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('employee_attendance_master', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                $table->index(['status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_attendance_master')) {
            Schema::table('employee_attendance_master', function (Blueprint $table) {
                if (Schema::hasColumn('employee_attendance_master', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
                if (Schema::hasColumn('employee_attendance_master', 'approved_by')) {
                    $table->dropColumn('approved_by');
                }
                if (Schema::hasColumn('employee_attendance_master', 'status')) {
                    $table->dropIndex(['status']);
                    $table->dropColumn('status');
                }
            });
        }
    }
};
