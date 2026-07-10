<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the table compatible with strict MySQL modes before adding columns.
        DB::statement("ALTER TABLE locataire_agence MODIFY updated_at DATETIME NULL DEFAULT NULL");

        Schema::table('locataire_agence', function (Blueprint $table) {
            if (! Schema::hasColumn('locataire_agence', 'periodicite_paiement_id')) {
                $table->unsignedBigInteger('periodicite_paiement_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            if (Schema::hasColumn('locataire_agence', 'periodicite_paiement_id')) {
                $table->dropColumn('periodicite_paiement_id');
            }
        });
    }
};
