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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_type'); // casual, sick, annual, compensatory, maternity, paternity, unpaid
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 5, 2); // Support half days
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('approver_remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('supporting_document')->nullable();
            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_period')->nullable(); // morning, afternoon
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index('tenant_uuid');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
