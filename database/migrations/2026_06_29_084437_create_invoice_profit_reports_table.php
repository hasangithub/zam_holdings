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
        Schema::create('invoice_profit_reports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_id'); // invoice reference

            $table->decimal('exchange_rate', 10, 2)->default(0);
            $table->decimal('fp_perkg', 10, 2)->default(0);

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_profit_reports');
    }
};
