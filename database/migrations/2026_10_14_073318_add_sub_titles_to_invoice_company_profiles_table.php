<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_company_profiles', function (Blueprint $table) {
            $table->string('sub_title_1')->nullable()->after('title');
            $table->string('sub_title_2')->nullable()->after('sub_title_1');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_company_profiles', function (Blueprint $table) {
            $table->dropColumn(['sub_title_1', 'sub_title_2']);
        });
    }
};