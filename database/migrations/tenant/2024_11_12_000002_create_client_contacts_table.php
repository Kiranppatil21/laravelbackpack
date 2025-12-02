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
        if (! Schema::hasTable('client_contacts')) {
            Schema::create('client_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->string('name', 191);
                $table->string('contact_no', 20);
                $table->foreignId('designation_id')->nullable()->constrained('designations')->onDelete('set null');
                $table->string('email', 191)->nullable();
                $table->boolean('send_sms')->default(false);
                $table->boolean('send_email')->default(false);
                $table->timestamps();
                
                $table->index(['client_id', 'send_sms']);
                $table->index(['client_id', 'send_email']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};