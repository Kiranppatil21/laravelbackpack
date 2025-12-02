<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_service_tax_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 12, 2);
            $table->string('service_type', 100)->default('Security Services');
            $table->string('tax_type', 50); // GST, SGST, CGST, IGST
            $table->decimal('tax_percent', 5, 2);
            $table->decimal('final_amount', 12, 2);
            $table->string('comment', 191)->nullable();
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('client_invoices')->onDelete('cascade');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_service_tax_details');
    }
};
