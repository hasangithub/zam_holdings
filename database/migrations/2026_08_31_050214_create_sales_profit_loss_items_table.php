<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_profit_loss_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_profit_loss_id')
                ->constrained('sales_profit_losses')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->nullable()
                ->constrained('items')
                ->nullOnDelete();

            // Snapshot of product/item information
            $table->string('item_name')->nullable();

            $table->decimal('sales_weight', 15, 3)
                ->default(0);

            $table->decimal('sales_price', 15, 2)
                ->default(0);

            $table->decimal('purchase_price', 15, 2)
                ->default(0);

            $table->decimal('other_expense_per_kg', 15, 2)
                ->default(0);

            $table->decimal('purchase_cost_per_kg', 15, 2)
                ->default(0);

            $table->decimal('total_cost', 15, 2)
                ->default(0);

            $table->decimal('sales_amount', 15, 2)
                ->default(0);

            $table->decimal('profit_loss', 15, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_profit_loss_items');
    }
};