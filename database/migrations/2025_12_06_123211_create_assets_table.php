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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('asset_name');
            $table->string('asset_code')->unique();
            $table->string('category'); // Electronics, Furniture, Vehicles, Equipment, etc.
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->string('status')->default('available'); // available, assigned, maintenance, retired
            $table->string('condition')->nullable(); // excellent, good, fair, poor
            $table->string('location')->nullable();
            $table->unsignedBigInteger('assigned_to_employee_id')->nullable();
            $table->unsignedBigInteger('assigned_to_client_id')->nullable();
            $table->date('assigned_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('maintenance_notes')->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('assigned_to_employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('assigned_to_client_id')->references('id')->on('clients')->onDelete('set null');
            $table->index('tenant_uuid');
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
