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
         Schema::create('sales_amendments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->date('amendment_date');
            $table->decimal('amount', 15, 2);
            $table->string('reason');

            $table->text('notes')->nullable();

            $table->enum('status', [
                'draft',
                'approved',
                'cancelled'
            ])->default('approved');


            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('journal_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_amendments');
    }
};
