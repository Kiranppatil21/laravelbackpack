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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('incident_number')->unique();
            $table->string('incident_type'); // theft, assault, fire, medical, accident, property-damage, suspicious-activity, breach
            $table->string('severity'); // low, medium, high, critical
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('reported_by_employee_id');
            $table->dateTime('incident_datetime');
            $table->string('location');
            $table->text('description');
            $table->text('action_taken');
            $table->string('status')->default('open'); // open, investigating, resolved, closed
            $table->boolean('police_notified')->default(false);
            $table->string('police_report_number')->nullable();
            $table->boolean('client_notified')->default(false);
            $table->dateTime('client_notified_at')->nullable();
            $table->text('client_response')->nullable();
            $table->json('witnesses')->nullable(); // Array of witness details
            $table->json('involved_parties')->nullable(); // Array of involved persons
            $table->string('evidence_photo_1')->nullable();
            $table->string('evidence_photo_2')->nullable();
            $table->string('evidence_photo_3')->nullable();
            $table->string('evidence_document')->nullable();
            $table->decimal('estimated_loss', 12, 2)->nullable();
            $table->boolean('insurance_claim')->default(false);
            $table->string('claim_reference')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // Investigating officer
            $table->text('investigation_notes')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            $table->foreign('reported_by_employee_id')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index('tenant_uuid');
            $table->index('status');
            $table->index('severity');
            $table->index('incident_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
