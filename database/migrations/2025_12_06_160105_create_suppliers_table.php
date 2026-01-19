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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('supplier_code')->unique();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('gstin')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('category')->nullable(); // Electronics, Uniforms, Equipment, etc.
            $table->string('payment_terms')->nullable(); // COD, Net 30, Net 60
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->decimal('outstanding_amount', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, inactive, blacklisted
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('tenant_uuid');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
