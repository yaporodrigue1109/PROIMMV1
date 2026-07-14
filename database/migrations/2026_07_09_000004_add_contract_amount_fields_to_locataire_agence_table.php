<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep legacy installs compatible with strict MySQL modes.
        DB::statement("ALTER TABLE locataire_agence MODIFY updated_at DATETIME NULL DEFAULT NULL");

        Schema::table('locataire_agence', function (Blueprint $table) {
            if (! Schema::hasColumn('locataire_agence', 'caution')) {
                $table->decimal('caution', 12, 2)->nullable()->after('loyer_net');
            }

            if (! Schema::hasColumn('locataire_agence', 'avance')) {
                $table->decimal('avance', 12, 2)->nullable()->after('caution');
            }

            if (! Schema::hasColumn('locataire_agence', 'agence')) {
                $table->decimal('agence', 12, 2)->nullable()->after('avance');
            }

            if (! Schema::hasColumn('locataire_agence', 'caution_cie')) {
                $table->decimal('caution_cie', 12, 2)->nullable()->after('agence');
            }

            if (! Schema::hasColumn('locataire_agence', 'caution_sodeci')) {
                $table->decimal('caution_sodeci', 12, 2)->nullable()->after('caution_cie');
            }

            if (! Schema::hasColumn('locataire_agence', 'frais_annexe')) {
                $table->decimal('frais_annexe', 12, 2)->nullable()->after('caution_sodeci');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            if (Schema::hasColumn('locataire_agence', 'frais_annexe')) {
                $table->dropColumn('frais_annexe');
            }

            if (Schema::hasColumn('locataire_agence', 'caution_sodeci')) {
                $table->dropColumn('caution_sodeci');
            }

            if (Schema::hasColumn('locataire_agence', 'caution_cie')) {
                $table->dropColumn('caution_cie');
            }

            if (Schema::hasColumn('locataire_agence', 'agence')) {
                $table->dropColumn('agence');
            }

            if (Schema::hasColumn('locataire_agence', 'avance')) {
                $table->dropColumn('avance');
            }

            if (Schema::hasColumn('locataire_agence', 'caution')) {
                $table->dropColumn('caution');
            }
        });
    }
};
