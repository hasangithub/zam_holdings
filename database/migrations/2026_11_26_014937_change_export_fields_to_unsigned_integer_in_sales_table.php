<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE sales
            MODIFY port_of_loading INT UNSIGNED NULL,
            MODIFY mode_of_payment INT UNSIGNED NULL,
            MODIFY mode_of_shipping INT UNSIGNED NULL,
            MODIFY country_of_origin INT UNSIGNED NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE sales
            MODIFY port_of_loading VARCHAR(255) NULL,
            MODIFY mode_of_payment VARCHAR(255) NULL,
            MODIFY mode_of_shipping VARCHAR(255) NULL,
            MODIFY country_of_origin VARCHAR(255) NULL
        ");
    }
};