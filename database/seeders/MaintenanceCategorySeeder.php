<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MaintenanceCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('maintenance_categories')) {
            return;
        }

        $now = now();

        $categories = [
            ['name' => 'Technique', 'description' => 'Interventions techniques generales.'],
            ['name' => 'Entretien', 'description' => 'Entretien courant et maintenance preventive.'],
            ['name' => 'Securite', 'description' => 'Serrurerie, alarme et controle d acces.'],
            ['name' => 'Batiment', 'description' => 'Travaux lies a la structure et au gros oeuvre.'],
            ['name' => 'Equipements', 'description' => 'Equipements techniques et appareils.'],
            ['name' => 'Logistique', 'description' => 'Mouvements, livraisons et assistance logistique.'],
        ];

        foreach ($categories as $category) {
            DB::table('maintenance_categories')->updateOrInsert(
                ['name' => $category['name']],
                [
                    'maintenance_category_id' => (string) Str::uuid(),
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
