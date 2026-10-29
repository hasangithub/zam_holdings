
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('opening_journal_entry_id')
                ->nullable()
                ->after('description');

            $table->unsignedBigInteger('fixed_asset_ledger_id')
                ->nullable()
                ->after('opening_journal_entry_id');

            $table->unsignedBigInteger('asset_sub_ledger_id')
                ->nullable()
                ->after('fixed_asset_ledger_id');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {});
    }
};
