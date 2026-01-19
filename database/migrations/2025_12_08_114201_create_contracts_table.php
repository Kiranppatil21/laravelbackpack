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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('contract_number')->unique();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('agency_id');
            $table->string('contract_type'); // security-services, manpower, facility-management, event-security
            $table->string('service_type'); // Armed/Unarmed/Mobile/Static/Event
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months');
            $table->integer('number_of_guards');
            $table->string('shift_pattern'); // 12-hour, 8-hour, 24-hour
            $table->decimal('monthly_contract_value', 12, 2);
            $table->decimal('per_guard_rate', 10, 2);
            $table->decimal('overtime_rate', 10, 2)->nullable();
            $table->string('billing_cycle'); // monthly, quarterly, annual
            $table->integer('payment_terms_days')->default(30); // Net 30, Net 60
            $table->string('status')->default('draft'); // draft, active, expired, renewed, cancelled, terminated
            $table->text('scope_of_work');
            $table->text('terms_and_conditions')->nullable();
            $table->text('special_instructions')->nullable();
            $table->string('contract_document')->nullable(); // PDF file
            $table->string('signed_contract')->nullable();
            $table->date('signed_date')->nullable();
            $table->string('client_signatory')->nullable();
            $table->string('agency_signatory')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->integer('renewal_notice_days')->default(30);
            $table->date('renewal_reminder_sent')->nullable();
            $table->unsignedBigInteger('renewed_from_contract_id')->nullable(); // Link to previous contract
            $table->text('cancellation_reason')->nullable();
            $table->date('cancelled_date')->nullable();
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->boolean('deposit_refunded')->default(false);
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('restrict');
            $table->foreign('renewed_from_contract_id')->references('id')->on('contracts')->onDelete('set null');
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
        Schema::dropIfExists('contracts');
    }
};
