<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visitors')) {
            Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('host_id')->nullable();
            $table->string('company')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_value')->nullable();
            $table->string('source')->nullable(); // 'reception','rfid','cctv','api'
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
