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
        Schema::create('landing_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id'); // sales.id
            $table->decimal('gross_weight',10,2);
            $table->decimal('exchange_rate',10,4);
            $table->decimal('air_freight_rate', 10, 2)->default(0);
            $table->decimal('air_freight_usd', 12, 2)->default(0);
            $table->decimal('air_freight_lkr', 12, 2)->default(0);
            $table->decimal('logistics_expenses', 12, 2)->default(0);
            $table->decimal('freight_logistics_perkg', 12, 2)->default(0);
            $table->decimal('packing_cost_perkg', 12, 2)->default(0);
            $table->decimal('total_cost_perkg', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_costs');
    }
};
