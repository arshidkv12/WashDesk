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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            $table->enum('item_type', ['product', 'custom_product', 'service']);
            
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null'); 

            $table->string('name')->nullable();
                        
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->string('unit')->default('piece');
            
            $table->integer('tax_rate')->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            
            $table->decimal('line_total', 15, 2)->virtualAs('quantity * unit_price');
            $table->decimal('line_tax', 15, 2)->virtualAs('quantity * unit_price * (tax_rate / 100)');
            $table->decimal('line_discount', 15, 2)->virtualAs('quantity * unit_price * (discount_percentage / 100)');
            $table->decimal('net_amount', 15, 2)->virtualAs('(quantity * unit_price) + (quantity * unit_price * (tax_rate / 100)) - (quantity * unit_price * (discount_percentage / 100))');
                        
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('invoice_id');
            $table->index('product_id');
            $table->index('item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
