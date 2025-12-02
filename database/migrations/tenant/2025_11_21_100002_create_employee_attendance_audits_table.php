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
        if (! Schema::hasTable('employee_attendance_audits')) {
            Schema::create('employee_attendance_audits', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('attendance_master_id')->index();
                $table->unsignedBigInteger('attendance_detail_id')->nullable()->index();
                $table->unsignedBigInteger('site_id')->nullable()->index();
                $table->unsignedBigInteger('changed_by')->nullable()->index();
                $table->string('action', 32); // create, update, delete, submit, approve, lock
                $table->json('before')->nullable();
                $table->json('after')->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();

                $table->foreign('attendance_master_id')->references('id')->on('employee_attendance_master')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_audits');
    }
};
