<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prossimite_proprietes') && Schema::hasColumn('prossimite_proprietes', 'agence_id')) {
            Schema::table('prossimite_proprietes', function (Blueprint $table) {
                $table->string('agence_id', 150)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Une proximité globale utilise agence_id = NULL : ne pas rendre la
        // colonne obligatoire automatiquement au risque de perdre ces données.
    }
};
