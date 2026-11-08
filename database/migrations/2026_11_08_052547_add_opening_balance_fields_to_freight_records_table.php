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
        Schema::table('freights', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')
                ->nullable()
                ->change();

            $table->boolean('is_opening_balance')
                ->default(false)
                ->after('sale_id');

            $table->string('invoice_id')
                ->nullable()
                ->after('is_opening_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freight_records', function (Blueprint $table) {
            //
        });
    }
};
