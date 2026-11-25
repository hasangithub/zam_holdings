<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_currency_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('freight_id');

            $table->unsignedBigInteger('branch_id');

            $table->string('currency', 3);

            $table->date('payment_date');

            $table->decimal('amount', 15, 2);

            $table->string('reference')->nullable();

            $table->text('note')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_currency_payments');
    }
};
