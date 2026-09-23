<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->nullable();

            // The user's branch/user-specific cashbook
            $table->foreignId('cashbook_sub_ledger_id')
                ->constrained('sub_ledgers')
                ->restrictOnDelete();

            $table->date('transaction_date');

            // in = Cash In / out = Cash Out
            $table->enum('type', ['in', 'out']);

            $table->decimal('amount', 15, 2);

            // Other accounting account
            $table->foreignId('ledger_id')
                ->constrained('ledgers')
                ->restrictOnDelete();

            // Optional because some ledgers may not use subledgers
            $table->foreignId('sub_ledger_id')
                ->nullable()
                ->constrained('sub_ledgers')
                ->restrictOnDelete();

            $table->string('reference')->nullable();

            $table->text('description')->nullable();

            // Journal generated for this transaction
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'branch_id',
                'transaction_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook_transactions');
    }
};