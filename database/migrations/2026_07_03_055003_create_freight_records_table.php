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
        Schema::create('freight_records', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->string('airway_no')->nullable();

            $table->string('consignor')->nullable();
            $table->string('consignee')->nullable();

            $table->decimal('net_weight', 10, 2)->nullable();
            $table->decimal('gross_weight', 10, 2)->nullable();

            $table->integer('boxes')->nullable();

            $table->decimal('exchange_rate', 10, 4)->nullable();

            $table->decimal('custom_usd', 10, 2)->nullable();
            $table->decimal('custom_lkr', 10, 2)->nullable();

            $table->decimal('freight_usd', 10, 2)->nullable();
            $table->decimal('freight_lkr', 10, 2)->nullable();

            $table->string('cusdec_no')->nullable();
            $table->string('booking')->nullable();
            $table->string('bank')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freight_records');
    }
};
