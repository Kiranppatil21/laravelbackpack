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
        // Drop dependent tables first to avoid foreign key constraint issues
        Schema::dropIfExists('employee_attendance_audits');
        Schema::dropIfExists('employee_attendance_details');
        Schema::dropIfExists('employee_attendance_master');

        Schema::create('employee_attendance_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('site_id')->index();
            $table->char('month', 7);
            $table->string('user_type', 50)->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'month', 'user_type']);
            $table->index(['status']);

            if (Schema::hasTable('clients') && Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('site_id')->references('id')->on('clients')->onDelete('cascade');
            }
        });

        Schema::create('employee_attendance_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_master_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedBigInteger('site_id')->index();
            $table->date('date');
            $table->enum('shift', ['1', '2', '3']);
            $table->boolean('is_present')->default(true);
            $table->boolean('is_ot')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['site_id', 'date']);
            $table->unique(['employee_id', 'date', 'shift']);

            if (Schema::hasTable('employee_attendance_master') && Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('attendance_master_id')->references('id')->on('employee_attendance_master')->onDelete('cascade');
            }
            if (Schema::hasTable('employees') && Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            }
            if (Schema::hasTable('clients') && Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('site_id')->references('id')->on('clients')->onDelete('cascade');
            }
        });

        Schema::create('employee_attendance_audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_master_id')->index();
            $table->unsignedBigInteger('attendance_detail_id')->nullable()->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('changed_by')->nullable()->index();
            $table->string('action', 32);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            if (Schema::hasTable('employee_attendance_master') && Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('attendance_master_id')->references('id')->on('employee_attendance_master')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_audits');
        Schema::dropIfExists('employee_attendance_details');
        Schema::dropIfExists('employee_attendance_master');
    }
};
