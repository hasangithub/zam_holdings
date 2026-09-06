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
        Schema::create('freights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('sale_id')->constrained('sales');
            $table->foreignId('freight_service_id')->constrained('freight_services');

            $table->date('date');
            $table->decimal('exchange_rate', 18, 4);
            $table->decimal('amount_usd', 18, 2);
            $table->decimal('amount_lkr', 18, 2);

            $table->decimal('total_paid', 18, 2)->default(0);
            $table->decimal('balance_amount', 18, 2)->default(0);

            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freights');
    }
};
