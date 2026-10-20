<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statement_of_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('statement_of_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'branch_id',
                'status',
                'submitted_at',
            ]);
        });
    }
};