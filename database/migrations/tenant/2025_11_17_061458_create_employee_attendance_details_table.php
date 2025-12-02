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
        if (! Schema::hasTable('employee_attendance_details')) {
            Schema::create('employee_attendance_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('attendance_master_id')->index(); // FK to master table
                $table->unsignedBigInteger('employee_id')->index(); // FK to employees.id
                $table->unsignedBigInteger('site_id')->index(); // FK to clients.id
                $table->date('date'); // Attendance date
                $table->enum('shift', ['1', '2', '3']); // Shift number
                $table->boolean('is_present')->default(true); // 1 = Present, 0 = Absent
                $table->boolean('is_ot')->default(false); // 1 = Overtime, 0 = No OT
                $table->timestamps();

                // Add foreign key constraints
                $table->foreign('attendance_master_id')->references('id')->on('employee_attendance_master')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('site_id')->references('id')->on('clients')->onDelete('cascade');
                
                // Add composite indexes for performance
                $table->index(['employee_id', 'date']);
                $table->index(['site_id', 'date']);
                $table->unique(['employee_id', 'date', 'shift']); // Prevent duplicate attendance for same employee/date/shift
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_details');
    }
};
