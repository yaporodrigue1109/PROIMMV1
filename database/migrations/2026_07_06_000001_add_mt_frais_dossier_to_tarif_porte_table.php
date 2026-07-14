<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarif_porte', function (Blueprint $table) {
            $table->decimal('mt_frais_dossier', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tarif_porte', function (Blueprint $table) {
            $table->dropColumn('mt_frais_dossier');
        });
    }
};
