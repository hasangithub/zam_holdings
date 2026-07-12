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
         Schema::table('sales', function (Blueprint $table) {
            $table->string('consignee_name')->nullable()->after('consignor');
            $table->text('consignee_address')->nullable()->after('consignee_name');
            $table->string('port_of_loading')->nullable()->after('consignee_address');
            $table->string('country_of_origin')->nullable()->after('port_of_loading');
            $table->string('mode_of_payment')->nullable()->after('country_of_origin');
            $table->string('mode_of_shipping')->nullable()->after('mode_of_payment');
            $table->string('flight_no')->nullable()->after('mode_of_shipping');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('consignee_name')->nullable()->after('name');
            $table->text('consignee_address')->nullable()->after('consignee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_and_customers_tables', function (Blueprint $table) {
            //
        });
    }
};
