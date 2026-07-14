<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some legacy installs keep `updated_at` with an invalid zero-date default.
        // Normalise it first so MySQL accepts subsequent ALTER TABLE statements.
        DB::statement("ALTER TABLE locataire_agence MODIFY updated_at DATETIME NULL DEFAULT NULL");

        Schema::table('locataire_agence', function (Blueprint $table) {
            if (!Schema::hasColumn('locataire_agence', 'pas_de_porte')) {
                $table->decimal('pas_de_porte', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('locataire_agence', 'montant_global_garantie')) {
                $table->decimal('montant_global_garantie', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('locataire_agence', 'date_signature_bail')) {
                $table->date('date_signature_bail')->nullable();
            }

            if (!Schema::hasColumn('locataire_agence', 'versements_depot_garantie')) {
                $table->json('versements_depot_garantie')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            if (Schema::hasColumn('locataire_agence', 'versements_depot_garantie')) {
                $table->dropColumn('versements_depot_garantie');
            }

            if (Schema::hasColumn('locataire_agence', 'date_signature_bail')) {
                $table->dropColumn('date_signature_bail');
            }

            if (Schema::hasColumn('locataire_agence', 'montant_global_garantie')) {
                $table->dropColumn('montant_global_garantie');
            }

            if (Schema::hasColumn('locataire_agence', 'pas_de_porte')) {
                $table->dropColumn('pas_de_porte');
            }
        });
    }
};
