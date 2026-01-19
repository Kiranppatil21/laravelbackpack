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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('transaction_type'); // purchase, issue, return, adjustment, transfer, maintenance
            $table->string('reference_type')->nullable(); // PurchaseOrder, Employee, Client, Asset
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('asset_id');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->unsignedBigInteger('issued_to_employee_id')->nullable();
            $table->unsignedBigInteger('issued_to_client_id')->nullable();
            $table->date('transaction_date');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('restrict');
            $table->foreign('issued_to_employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('issued_to_client_id')->references('id')->on('clients')->onDelete('set null');
            $table->index('tenant_uuid');
            $table->index('transaction_type');
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
