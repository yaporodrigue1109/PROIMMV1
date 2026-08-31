<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE transaction_agences MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');

        foreach (['locataire_id', 'proprietaire_id', 'propriete_id', 'batiment_id', 'porte_id'] as $column) {
            DB::statement("ALTER TABLE transaction_agences MODIFY {$column} VARCHAR(150) NULL");
            DB::table('transaction_agences')->where($column, '')->update([$column => null]);
        }

        DB::statement('ALTER TABLE acheteurs MODIFY telephone2 VARCHAR(30) NULL');
        DB::statement('ALTER TABLE acheteurs MODIFY type_piece_id INT NULL');

        foreach (['propriete_id', 'batiment_id', 'lot_id', 'porte_id'] as $column) {
            DB::statement("ALTER TABLE ventes_biens MODIFY {$column} VARCHAR(150) NULL");
        }
    }

    public function down(): void
    {
        // Les colonnes restent volontairement nullables : remettre les contraintes
        // historiques rendrait impossibles les ventes par lot, propriété ou porte.
    }
};
