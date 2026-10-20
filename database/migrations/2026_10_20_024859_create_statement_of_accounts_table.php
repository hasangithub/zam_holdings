<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statement_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('as_of_date');
            $table->decimal('total_assets', 18, 2)->default(0);
            $table->decimal('total_liabilities', 18, 2)->default(0);
            $table->decimal('difference', 18, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('as_of_date');
        });

        Schema::create('statement_of_account_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('statement_of_account_id');

            // Store the original IDs for reference; snapshots preserve old names and balances.
            $table->unsignedBigInteger('account_type_id')->nullable();
            $table->unsignedBigInteger('account_group_id')->nullable();

            $table->string('account_type_name');
            $table->string('account_group_name');
            $table->string('section'); // asset or liability

            $table->decimal('ledger_total', 18, 2)->default(0);
            $table->decimal('adjustment_total', 18, 2)->default(0);
            $table->decimal('adjusted_total', 18, 2)->default(0);

            // Stores the ledger names, codes, and balances as they were when saved.
            $table->json('ledger_balances')->nullable();

            $table->timestamps();

            $table->index(['statement_of_account_id']);
        });

        Schema::create('statement_of_account_adjustments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('statement_of_account_group_id');

            $table->string('name');
            $table->decimal('amount', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statement_of_account_adjustments');
        Schema::dropIfExists('statement_of_account_groups');
        Schema::dropIfExists('statement_of_accounts');
    }
};