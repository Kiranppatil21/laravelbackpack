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
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('employee_salary_security_guard', 10, 2)->nullable();
            $table->decimal('employee_salary_supervisor', 10, 2)->nullable();
            $table->decimal('employee_salary_field_officer', 10, 2)->nullable();
            $table->decimal('employee_salary_manager_staff', 10, 2)->nullable();
            $table->decimal('employee_salary_watchman', 10, 2)->nullable();
            $table->decimal('employee_salary_security_officer', 10, 2)->nullable();
            $table->decimal('employee_salary_team_leader', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'employee_salary_security_guard',
                'employee_salary_supervisor',
                'employee_salary_field_officer',
                'employee_salary_manager_staff',
                'employee_salary_watchman',
                'employee_salary_security_officer',
                'employee_salary_team_leader',
            ]);
        });
    }
};
