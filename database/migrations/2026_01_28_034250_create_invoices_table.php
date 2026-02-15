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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedInteger('invoice_no');
            $table->unique(['user_id', 'invoice_no'], 'uniq_user_invoice');

            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            // Financial totals
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            
            // Status
            $table->string('status', 50)->default('draft');
                    
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('customer_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
