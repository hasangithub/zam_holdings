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
         Schema::table('purchase_payments', function (Blueprint $table) {

            /**
             * STEP 1: Add id column as PRIMARY KEY
             * (only if table was created without id)
             */
            if (!Schema::hasColumn('purchase_payments', 'id')) {
                $table->bigIncrements('id')->first();
            }

            /**
             * STEP 2: Drop purchase_id if exists
             */
            if (Schema::hasColumn('purchase_payments', 'purchase_id')) {
                $table->dropForeign(['purchase_id']);
                $table->dropColumn('purchase_id');
            }

            /**
             * STEP 3: Add supplier_id (ledger-based)
             */
            if (!Schema::hasColumn('purchase_payments', 'supplier_id')) {
                $table->foreignId('supplier_id')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            //
        });
    }
};
