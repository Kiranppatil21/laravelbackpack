<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('employee_id');
            $table->integer('duty_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('overtime_rate', 10, 2)->default(0);
            $table->decimal('payment', 12, 2)->default(0);
            $table->decimal('overtime_payment', 12, 2)->default(0);
            $table->decimal('total_payment', 12, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('client_invoices')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->index(['invoice_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_employees');
    }
};
