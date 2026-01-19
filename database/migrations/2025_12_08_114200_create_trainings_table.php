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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('training_name');
            $table->string('training_code')->unique();
            $table->string('category'); // security, safety, first-aid, fire-fighting, customer-service, technical
            $table->text('description');
            $table->string('trainer_name')->nullable();
            $table->string('trainer_contact')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_hours');
            $table->string('venue');
            $table->integer('max_participants')->nullable();
            $table->decimal('cost_per_participant', 10, 2)->nullable();
            $table->string('status')->default('scheduled'); // scheduled, ongoing, completed, cancelled
            $table->string('certificate_template')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->integer('validity_months')->nullable(); // For certifications
            $table->text('materials_provided')->nullable();
            $table->timestamps();
            
            $table->index('tenant_uuid');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
        
        // Training Participants table
        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('attendance_status')->default('registered'); // registered, attended, absent, completed
            $table->integer('score')->nullable(); // For assessments
            $table->string('grade')->nullable(); // Pass/Fail or A/B/C/D
            $table->boolean('certificate_issued')->default(false);
            $table->string('certificate_number')->nullable();
            $table->date('certificate_issued_date')->nullable();
            $table->date('certificate_expiry_date')->nullable();
            $table->text('feedback')->nullable();
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->timestamps();
            
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->index(['training_id', 'employee_id']);
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_participants');
        Schema::dropIfExists('trainings');
    }
};
