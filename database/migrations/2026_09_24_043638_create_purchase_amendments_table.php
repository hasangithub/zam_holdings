<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_amendments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Purchase
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Supplier
            |--------------------------------------------------------------------------
            */

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Amendment Information
            |--------------------------------------------------------------------------
            */

            $table->date('amendment_date');

            /*
             * Positive amount:
             *   Supplier payable increases
             *
             * Negative amount:
             *   Supplier payable decreases
             */
            $table->decimal('amount', 15, 2);


            $table->string('reason');

            $table->text('notes')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'approved',
                'cancelled'
            ])->default('approved');


            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

    

            /*
            |--------------------------------------------------------------------------
            | Accounting
            |--------------------------------------------------------------------------
            */

            $table->foreignId('journal_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'supplier_id',
                'amendment_date'
            ]);

            $table->index([
                'purchase_id',
                'status'
            ]);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purchase_amendments');
    }
};