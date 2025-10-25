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
        if (! Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                if (Schema::hasTable('tenants')) {
                    $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
                } else {
                    $table->unsignedBigInteger('tenant_id')->nullable();
                }
                $table->string('name');
                $table->string('email')->nullable()->index();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
