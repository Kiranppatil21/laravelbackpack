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
        Schema::create('client_designation_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('designation'); // Security Guard, Supervisor, Manager, etc.
            $table->decimal('client_rate_per_day', 10, 2)->default(0); // Rate client pays per day
            $table->decimal('agency_rate_per_day', 10, 2)->default(0); // Rate agency gets per day
            $table->decimal('client_ot_rate_per_hour', 10, 2)->default(0); // OT rate client pays
            $table->decimal('agency_ot_rate_per_hour', 10, 2)->default(0); // OT rate agency gets
            $table->timestamps();

            // Foreign keys
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate designation rates for same client
            $table->unique(['client_id', 'designation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_designation_rates');
    }
};
