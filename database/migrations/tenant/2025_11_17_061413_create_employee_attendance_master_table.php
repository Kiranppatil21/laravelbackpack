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
        if (! Schema::hasTable('employee_attendance_master')) {
            Schema::create('employee_attendance_master', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('site_id')->index(); // Foreign key to clients.id
                $table->char('month', 7); // e.g., "2025-11"
                $table->string('user_type', 50)->nullable(); // Guard/Field Officer/Manager/Supervisor
                $table->unsignedBigInteger('created_by')->nullable(); // Admin ID
                $table->timestamps();

                // Add foreign key constraints
                $table->foreign('site_id')->references('id')->on('clients')->onDelete('cascade');
                
                // Add composite index for performance
                $table->index(['site_id', 'month', 'user_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_master');
    }
};
