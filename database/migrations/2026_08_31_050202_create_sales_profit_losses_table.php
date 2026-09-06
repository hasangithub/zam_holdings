<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_profit_losses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->decimal('total_sales_weight', 15, 3)
                ->default(0);

            $table->decimal('total_cost', 15, 2)
                ->default(0);

            $table->decimal('total_sales_amount', 15, 2)
                ->default(0);

            $table->decimal('total_profit_loss', 15, 2)
                ->default(0);

            $table->timestamps();

            // Only one P&L record for each sales invoice
            $table->unique('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_profit_losses');
    }
};