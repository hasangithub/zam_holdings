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
        Schema::create('purchase_inventory_items', function (Blueprint $table) {
              $table->id();

            $table->foreignId('purchase_inventory_id');
            $table->foreignId('item_id');
            $table->decimal('qty', 10, 2);
            $table->decimal('remaining_qty', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_inventory_items');
    }
};
