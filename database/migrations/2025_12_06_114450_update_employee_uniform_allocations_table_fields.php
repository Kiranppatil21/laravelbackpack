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
        Schema::table('employee_uniform_allocations', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['client_id']);
            
            // Drop old columns that don't match the form
            $table->dropColumn(['item_name', 'quantity', 'rate', 'sub_total', 'client_id']);
            
            // Add new columns that match the form
            $table->string('item_type')->after('employee_id'); // Type of uniform item
            $table->string('size')->nullable()->after('item_type'); // Size (S, M, L, XL, etc.)
            $table->date('date_issued')->nullable()->after('size'); // When it was issued
            $table->string('condition')->default('new')->after('date_issued'); // new, good, fair, poor
            $table->text('notes')->nullable()->after('condition'); // Additional notes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_uniform_allocations', function (Blueprint $table) {
            // Restore old columns
            $table->unsignedBigInteger('client_id')->after('employee_id');
            $table->string('item_name')->after('client_id');
            $table->integer('quantity')->after('item_name');
            $table->decimal('rate', 10, 2)->after('quantity');
            $table->decimal('sub_total', 10, 2)->after('rate');
            
            // Drop new columns
            $table->dropColumn(['item_type', 'size', 'date_issued', 'condition', 'notes']);
            
            // Restore foreign key
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }
};
