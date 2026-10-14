<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->renameColumn('user_id', 'employee_id');
        });

        Schema::table('salary_advances', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('salary_advances', function (Blueprint $table) {
            $table->renameColumn('employee_id', 'user_id');
        });

        Schema::table('salary_advances', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }
};