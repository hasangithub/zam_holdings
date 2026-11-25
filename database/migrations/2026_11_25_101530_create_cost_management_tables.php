
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'name']);
        });

        Schema::create('cost_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('cost_category_id')->constrained('cost_categories');

            $table->string('name');

            // Automatically created accounting accounts
            $table->foreignId('ledger_id')->constrained('ledgers');
            $table->foreignId('accrued_expense_sub_ledger_id')
                ->constrained('sub_ledgers');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'cost_category_id', 'name']);
        });

        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('cost_category_id')
                ->constrained('cost_categories');

            $table->foreignId('cost_sub_category_id')
                ->constrained('cost_sub_categories');

            $table->date('cost_date');
            $table->decimal('amount', 15, 2);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('cost_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_id')->constrained('costs');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);

            $table->foreignId('cashbook_sub_ledger_id')
                ->constrained('sub_ledgers');

            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_payments');
        Schema::dropIfExists('costs');
        Schema::dropIfExists('cost_sub_categories');
        Schema::dropIfExists('cost_categories');
    }
};
