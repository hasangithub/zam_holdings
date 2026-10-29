<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freights', function (Blueprint $table) {
            $table->unsignedBigInteger('opening_journal_entry_id')
                ->nullable()
                ->after('balance_amount');
        });
    }

    public function down(): void
    {
        Schema::table('freights', function (Blueprint $table) {
           
        });
    }
};