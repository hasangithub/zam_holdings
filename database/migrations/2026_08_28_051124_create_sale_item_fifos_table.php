<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_item_fifos', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Sale Item
            |--------------------------------------------------------------------------
            */
            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Purchase Item / FIFO Batch
            |--------------------------------------------------------------------------
            */
            $table->foreignId('purchase_item_id')
                ->constrained('purchase_items')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Quantity consumed from this purchase batch
            |--------------------------------------------------------------------------
            */
            $table->decimal('qty', 15, 3);

            /*
            |--------------------------------------------------------------------------
            | Cost used for COGS
            |--------------------------------------------------------------------------
            |
            | Store the cost at the time of sale so historical COGS
            | does not change if the purchase record is later modified.
            |
            */
            $table->decimal('unit_cost', 15, 4);

            /*
            |--------------------------------------------------------------------------
            | Total cost for this FIFO allocation
            |--------------------------------------------------------------------------
            */
            $table->decimal('total_cost', 15, 4);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('sale_item_id');
            $table->index('purchase_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_fifos');
    }
};