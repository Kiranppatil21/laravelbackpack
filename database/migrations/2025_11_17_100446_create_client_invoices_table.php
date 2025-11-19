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
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique();
            $table->unsignedBigInteger('client_id');
            $table->char('month', 7); // YYYY-MM format
            $table->date('bill_date');
            $table->decimal('invoice_amount', 12, 2)->default(0);
            $table->decimal('other_amount_with_tax', 12, 2)->default(0);
            $table->decimal('other_amount_without_tax', 12, 2)->default(0);
            $table->decimal('service_charge_percent', 5, 2)->default(0);
            $table->decimal('service_charge_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('cst_amount', 12, 2)->default(0);
            $table->decimal('gross_bill_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->text('comments')->nullable();
            $table->string('monthly_comment', 191)->nullable();
            $table->boolean('send_mail')->default(false);
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->index(['client_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_invoices');
    }
};
