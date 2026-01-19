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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid');
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('status')->default('pending'); // pending, approved, ordered, partial, received, cancelled
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('shipping_address')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->index('tenant_uuid');
            $table->index('status');
            $table->index('order_date');
        });
        
        // Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('asset_id')->nullable(); // Link to existing asset for reorder
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('received_quantity', 10, 2)->default(0);
            $table->string('unit')->default('pcs'); // pcs, kg, ltr, box
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
