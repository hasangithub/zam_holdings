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
        Schema::table('purchases_and_purchase_inventories', function (Blueprint $table) {
            Schema::table('purchases', function (Blueprint $table) {

                $table->dropColumn([
                    'paid_amount',
                    'balance_amount',
                    'payment_status',
                ]);
            });

            Schema::table('purchase_inventories', function (Blueprint $table) {

                $table->dropColumn([
                    'paid_amount',
                    'balance_amount',
                    'payment_status',
                ]);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases_and_purchase_inventories', function (Blueprint $table) {
            //
        });
    }
};
