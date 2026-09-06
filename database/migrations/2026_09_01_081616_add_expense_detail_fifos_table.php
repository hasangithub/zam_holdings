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
        Schema::create('expense_detail_fifos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_detail_id')
                ->constrained('expense_details')
                ->cascadeOnDelete();

            $table->foreignId('purchase_inventory_item_id')
                ->constrained('purchase_inventory_items')
                ->restrictOnDelete();

            $table->decimal('qty', 15, 3);
            $table->decimal('unit_cost', 15, 4);

            $table->timestamps();

            $table->index('purchase_inventory_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
