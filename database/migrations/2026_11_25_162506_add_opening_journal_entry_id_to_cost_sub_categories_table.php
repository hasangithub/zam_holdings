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
        Schema::table('cost_sub_categories', function (Blueprint $table) {
            $table->foreignId('opening_journal_entry_id')
                ->nullable()
                ->after('accrued_expense_sub_ledger_id')
                ->constrained('journal_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cost_sub_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opening_journal_entry_id');
        });
    }
};
