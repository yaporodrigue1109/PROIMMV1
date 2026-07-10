<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeriodicitePaiementSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('periodicite_paiements')) {
            return;
        }

        $now = now();

        $items = [
            ['id' => 1, 'name' => 'Journalier', 'description' => 'Paiement effectué chaque jour'],
            ['id' => 2, 'name' => 'Hebdomadaire', 'description' => 'Paiement effectué chaque semaine'],
            ['id' => 3, 'name' => 'Mensuel', 'description' => 'Paiement effectué chaque mois'],
            ['id' => 4, 'name' => 'Bimestriel', 'description' => 'Paiement effectué tous les deux mois'],
            ['id' => 5, 'name' => 'Trimestriel', 'description' => 'Paiement effectué tous les trois mois'],
            ['id' => 6, 'name' => 'Annuel', 'description' => 'Paiement effectué chaque année'],
        ];

        foreach ($items as $item) {
            DB::table('periodicite_paiements')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'is_actif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
