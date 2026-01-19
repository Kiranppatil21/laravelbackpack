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
        if (!Schema::hasTable('client_taxes')) {
            Schema::create('client_taxes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->enum('status', ['active', 'inactive', 'pending', 'expired'])->default('active');
                $table->string('tax_type', 50); // GST, TDS, VAT, etc.
                $table->decimal('percentage', 5, 2);
                $table->string('tax_number', 50)->nullable();
                $table->timestamps();
                
                $table->index(['client_id', 'status']);
                $table->index(['client_id', 'tax_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_taxes');
    }
};
