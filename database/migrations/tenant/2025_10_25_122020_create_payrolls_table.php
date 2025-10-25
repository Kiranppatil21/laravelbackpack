<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->nullable()->index();
                $table->date('period_start')->nullable()->index();
                $table->date('period_end')->nullable()->index();
                $table->decimal('gross', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('net', 10, 2)->default(0);
                $table->string('status')->default('pending')->index();
                $table->dateTime('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
