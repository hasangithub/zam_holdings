<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_references', function (Blueprint $table) {

            $table->id();

            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')
                ->cascadeOnDelete();

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->enum('action', [
                'created',
                'updated',
                'reversed',
                'cancelled',
            ])->default('created');

            $table->foreignId('reversal_of_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['model_type', 'model_id'],
                'jer_model_index'
            );

            $table->index('reversal_of_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_references');
    }
};
