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
        Schema::create('packing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('actual_weight', 10, 2)->default(0);    
            $table->decimal('reject', 10, 2)->default(0);
            $table->decimal('market', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_items');
    }
};
