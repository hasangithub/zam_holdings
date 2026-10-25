
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('qty', 15, 3)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('qty', 15, 3)->change();
            $table->decimal('remaining_qty', 15, 3)->change();
        });
    }

    public function down(): void
    {
        // Change back to your original column definitions if needed.
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('qty', 15, 2)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('qty', 15, 2)->change();
            $table->decimal('remaining_qty', 15, 2)->change();
        });
    }
};
