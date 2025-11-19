<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('tax_type', 50); // SGST, CGST, IGST
            $table->decimal('tax_percent', 5, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->string('tax_no', 50)->nullable();
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('client_invoices')->onDelete('cascade');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_taxes');
    }
};
