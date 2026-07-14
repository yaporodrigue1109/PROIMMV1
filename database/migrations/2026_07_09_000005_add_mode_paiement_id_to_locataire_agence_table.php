<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE locataire_agence MODIFY updated_at DATETIME NULL DEFAULT NULL");

        Schema::table('locataire_agence', function (Blueprint $table) {
            if (! Schema::hasColumn('locataire_agence', 'mode_paiement_id')) {
                $table->unsignedBigInteger('mode_paiement_id')->nullable()->after('periodicite_paiement_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            if (Schema::hasColumn('locataire_agence', 'mode_paiement_id')) {
                $table->dropColumn('mode_paiement_id');
            }
        });
    }
};
