<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModePaiementSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('mode_paiements')) {
            return;
        }

        $now = now();

        $items = [
            ['id' => 1, 'name' => 'Espèces', 'description' => 'Paiement en espèces'],
            ['id' => 2, 'name' => 'Virement', 'description' => 'Virement bancaire'],
            ['id' => 3, 'name' => 'Mobile Money', 'description' => 'Paiement mobile money'],
            ['id' => 4, 'name' => 'Chèque', 'description' => 'Paiement par chèque'],
        ];

        foreach ($items as $item) {
            DB::table('mode_paiements')->updateOrInsert(
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
