<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visit_logs')) {
            Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->unsignedBigInteger('host_id')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->string('external_id')->nullable()->index(); // id from IoT device
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};
