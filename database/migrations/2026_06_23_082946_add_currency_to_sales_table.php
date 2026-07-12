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
            $table->string('currency', 3) ->default('LKR') ->after('customer_id'); 
            $table->decimal('exchange_rate', 10, 4) ->default(1) ->after('currency'); 
            $table->decimal('total_foreign', 10, 2) ->default(0) ->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //
        });
    }
};
