<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE loyer
                MODIFY date_limit_paiement TIMESTAMP NULL DEFAULT NULL,
                MODIFY updated_at TIMESTAMP NULL DEFAULT NULL,
                ADD UNIQUE loyer_unique_mensuel
                    (agence_id, locataire_id, porte_id, mois_paiement, annee_paiement)
        SQL);
    }

    public function down(): void
    {
        Schema::table('loyer', function (Blueprint $table) {
            $table->dropUnique('loyer_unique_mensuel');
        });
    }
};
