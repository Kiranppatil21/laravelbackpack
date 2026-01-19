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
        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('is_consumable')->default(false)->after('asset_code');
            $table->decimal('stock_quantity', 10, 2)->default(0)->after('is_consumable');
            $table->string('unit')->default('pcs')->after('stock_quantity'); // pcs, kg, ltr, box
            $table->decimal('min_stock_level', 10, 2)->nullable()->after('unit');
            $table->decimal('max_stock_level', 10, 2)->nullable()->after('min_stock_level');
            $table->decimal('reorder_level', 10, 2)->nullable()->after('max_stock_level');
            $table->string('storage_location')->nullable()->after('location');
            $table->string('barcode')->nullable()->unique()->after('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'is_consumable',
                'stock_quantity',
                'unit',
                'min_stock_level',
                'max_stock_level',
                'reorder_level',
                'storage_location',
                'barcode'
            ]);
        });
    }
};
