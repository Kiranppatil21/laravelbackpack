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
        Schema::create('client_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('tax_status', ['active', 'inactive', 'applicable'])->default('applicable');
            $table->string('tax_type', 50); // GST, IGST, CGST, SGST, Service Tax, etc.
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'tax_status']);
            $table->index(['client_id', 'tax_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_taxes');
    }
};