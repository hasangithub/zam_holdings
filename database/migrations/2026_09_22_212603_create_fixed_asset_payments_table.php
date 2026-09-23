<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_payments', function (Blueprint $table) {

            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            $table->date('payment_date');

            // Cash / Bank subledger
            $table->foreignId('payment_sub_ledger_id')
                ->nullable()
                ->constrained('sub_ledgers')
                ->nullOnDelete();

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_payments');
    }
};