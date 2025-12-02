<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetryFieldsToRazorpayPaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('razorpay_payments')) {
            if (! Schema::hasColumn('razorpay_payments', 'retry_count')) {
                Schema::table('razorpay_payments', function (Blueprint $table) {
                    $table->integer('retry_count')->default(0)->after('raw');
                    $table->timestamp('last_retry_at')->nullable()->after('retry_count');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('razorpay_payments')) {
            Schema::table('razorpay_payments', function (Blueprint $table) {
                if (Schema::hasColumn('razorpay_payments', 'last_retry_at')) {
                    $table->dropColumn('last_retry_at');
                }
                if (Schema::hasColumn('razorpay_payments', 'retry_count')) {
                    $table->dropColumn('retry_count');
                }
            });
        }
    }
}
