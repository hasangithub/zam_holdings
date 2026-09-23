<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {

            $table->id();
            $table->foreignId('branch_id')->nullable();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->date('purchase_date');
            $table->decimal('amount', 15, 2);
            $table->foreignId('supplier_id') ->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'active',
                'cancelled'
            ])->default('active');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};