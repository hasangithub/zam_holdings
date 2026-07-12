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
        Schema::create('invoice_profit_report_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('item_id');

            $table->decimal('sale_price_usd', 10, 2)->default(0);
            $table->decimal('market_price', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_profit_report_items');
    }
};
