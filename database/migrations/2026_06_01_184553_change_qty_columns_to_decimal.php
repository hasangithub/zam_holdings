<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('decimal', function (Blueprint $table) {
            DB::statement('ALTER TABLE purchase_items MODIFY qty DECIMAL(10,2) NOT NULL');
            DB::statement('ALTER TABLE purchase_items MODIFY remaining_qty DECIMAL(10,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE sale_items MODIFY qty DECIMAL(10,2) NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decimal', function (Blueprint $table) {
            //
        });
    }
};
