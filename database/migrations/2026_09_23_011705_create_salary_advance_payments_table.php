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
        Schema::create('salary_advance_payments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('salary_advance_id')
                ->constrained('salary_advances')
                ->cascadeOnDelete();

            $table->date('payment_date');

            $table->decimal('amount', 15, 2);

            $table->foreignId('payment_sub_ledger_id')
                ->constrained('sub_ledgers');

            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->string('reference')->nullable();

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advance_payments');
    }
};
