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
        Schema::table('core_tables', function (Blueprint $table) {
             // PURCHASES
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });

            // CUSTOMERS
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });

            // SUPPLIERS
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });

            // SALES
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_tables', function (Blueprint $table) {
            //
        });
    }
};
