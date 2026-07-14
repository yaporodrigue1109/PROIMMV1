<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tarif_porte MODIFY porte_id CHAR(36) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tarif_porte MODIFY porte_id BIGINT UNSIGNED NOT NULL");
    }
};
