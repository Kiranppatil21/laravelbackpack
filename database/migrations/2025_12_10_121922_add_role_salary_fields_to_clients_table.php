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
            $table->decimal('role_salary_security_guard', 10, 2)->nullable();
            $table->decimal('role_salary_supervisor', 10, 2)->nullable();
            $table->decimal('role_salary_field_officer', 10, 2)->nullable();
            $table->decimal('role_salary_manager_staff', 10, 2)->nullable();
            $table->decimal('role_salary_watchman', 10, 2)->nullable();
            $table->decimal('role_salary_security_officer', 10, 2)->nullable();
            $table->decimal('role_salary_team_leader', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'role_salary_security_guard',
                'role_salary_supervisor',
                'role_salary_field_officer',
                'role_salary_manager_staff',
                'role_salary_watchman',
                'role_salary_security_officer',
                'role_salary_team_leader',
            ]);
        });
    }
};
