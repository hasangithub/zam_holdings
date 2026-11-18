<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_currency_opening_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('branch_id');

            $table->string('currency', 3);

            $table->date('opening_date');

            $table->decimal('amount', 15, 2)->default(0);

            $table->string('note')->nullable();

            $table->timestamps();

            $table->unique(
                ['customer_id', 'currency'],
                'customer_currency_opening_unique'
            );

            $table->index(['branch_id', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_currency_opening_balances');
    }
};
