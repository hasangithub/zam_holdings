<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->unsignedBigInteger('salary_advance_sub_ledger_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->unsignedBigInteger('salary_advance_sub_ledger_id')->nullable(false)->change();
        });
    }
};
