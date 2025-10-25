<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRazorpayPaymentsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('razorpay_payments')) {
            Schema::create('razorpay_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_id')->unique();
                $table->string('order_id')->nullable();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->bigInteger('amount')->nullable();
                $table->string('currency')->nullable();
                $table->json('raw')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('razorpay_payments');
    }
}
