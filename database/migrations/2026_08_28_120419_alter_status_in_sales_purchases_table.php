<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('status')
                ->default('posted')
                ->before('created_at');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('status')
                ->default('posted')
                ->before('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};