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
        Schema::create('company_job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department');
            $table->string('location');
            $table->enum('type', ['full-time', 'part-time', 'contract', 'internship']);
            $table->string('experience_level');
            $table->text('description');
            $table->json('requirements'); // Store as JSON array
            $table->string('salary_range')->nullable();
            $table->enum('status', ['active', 'inactive', 'filled'])->default('active');
            $table->string('contact_email')->nullable();
            $table->integer('priority')->default(0); // For ordering
            $table->date('application_deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_job_openings');
    }
};
