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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('shift_name'); // Morning, Evening, Night, General
            $table->string('shift_code')->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_hours');
            $table->decimal('ot_after_hours', 5, 2)->nullable();
            $table->boolean('is_night_shift')->default(false);
            $table->decimal('night_allowance', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('tenant_uuid');
            $table->index('is_active');
        });
        
        // Shift Assignments table
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->date('assignment_date');
            $table->string('status')->default('scheduled'); // scheduled, completed, no-show, cancelled
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->index('tenant_uuid');
            $table->index('assignment_date');
            $table->index('status');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shifts');
    }
};
