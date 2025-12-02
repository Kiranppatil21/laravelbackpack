<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_additional_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->string('comment', 191)->nullable();
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('client_invoices')->onDelete('cascade');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_additional_charges');
    }
};
