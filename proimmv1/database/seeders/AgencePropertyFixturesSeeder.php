<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AgencePropertyFixturesSeeder extends Seeder
{
    private string $agenceId = '22222222-2222-2222-2222-222222222222';
    private string $adminId = '11111111-1111-1111-1111-111111111111';
    private string $agentId = '33333333-3333-3333-3333-333333333333';

    public function run(): void
    {
        $this->seedReferenceData();

        $agenceIds = $this->getAgenceIds();

        foreach ($agenceIds as $agenceId) {
            $this->seedTypePortes();
            $this->seedTypeProprietes($agenceId);
            $this->seedEquipements($agenceId);
            $this->seedProximites($agenceId);
            $this->seedProprietaires($agenceId);
            $this->seedLots($agenceId);
            $this->seedProperties($agenceId);
        }
    }

    private function seedReferenceData(): void
    {
        $this->upsertIfTableExists('regions', ['id' => 1], [
            'id' => 1,
            'name' => 'Abidjan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('regions', ['id' => 2], [
            'id' => 2,
            'name' => 'Bouaké',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('villes', ['id' => 1], [
            'id' => 1,
            'region_id' => 1,
            'name' => 'Cocody',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('villes', ['id' => 2], [
            'id' => 2,
            'region_id' => 1,
            'name' => 'Marcory',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('type_pieces', ['id' => 1], [
            'id' => 1,
            'type_pieces_id' => 1,
            'name' => 'CNI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('type_pieces', ['id' => 2], [
            'id' => 2,
            'type_pieces_id' => 2,
            'name' => 'Passeport',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('genres', ['id' => 1], [
            'id' => 1,
            'name' => 'Homme',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->upsertIfTableExists('genres', ['id' => 2], [
            'id' => 2,
            'name' => 'Femme',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedTypePortes(): void
    {
        $items = [
            ['id' => 1, 'libelle' => 'Standard', 'description' => 'Porte standard'],
            ['id' => 2, 'libelle' => 'Double', 'description' => 'Porte double'],
            ['id' => 3, 'libelle' => 'Coulissante', 'description' => 'Porte coulissante'],
            ['id' => 4, 'libelle' => 'Autre', 'description' => 'Autre type de porte'],
        ];

        foreach ($items as $item) {
            $this->upsertIfTableExists('type_porte', ['type_porte_id' => $item['id']], [
                'type_porte_id' => $item['id'],
                'libelle' => $item['libelle'],
                'description' => $item['description'],
            ]);
        }
    }

    private function seedTypeProprietes(string $agenceId): void
    {
        $types = [
            ['id' => 1, 'name' => 'Appartement', 'description' => 'Logement en immeuble'],
            ['id' => 2, 'name' => 'Villa', 'description' => 'Maison individuelle'],
            ['id' => 3, 'name' => 'Studio', 'description' => 'Petit logement indépendant'],
        ];

        foreach ($types as $type) {
            $this->upsertIfTableExists('type_proprietes', ['agence_id' => $agenceId, 'name' => $type['name']], [
                'id' => $type['id'],
                'agence_id' => $agenceId,
                'name' => $type['name'],
                'description' => $type['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedEquipements(string $agenceId): void
    {
        $items = [
            ['id' => 1, 'name' => 'Climatisation', 'description' => 'Présence de climatisation'],
            ['id' => 2, 'name' => 'Parking', 'description' => 'Place de parking disponible'],
            ['id' => 3, 'name' => 'Ascenseur', 'description' => 'Ascenseur fonctionnel'],
        ];

        foreach ($items as $item) {
            $this->upsertIfTableExists('equipement_proprietes', ['agence_id' => $agenceId, 'name' => $item['name']], [
                'id' => $item['id'],
                'agence_id' => $agenceId,
                'name' => $item['name'],
                'description' => $item['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedProximites(string $agenceId): void
    {
        $items = [
            ['id' => 1, 'name' => 'École', 'description' => 'Établissement scolaire à proximité'],
            ['id' => 2, 'name' => 'Marché', 'description' => 'Marché de quartier à proximité'],
            ['id' => 3, 'name' => 'Pharmacie', 'description' => 'Pharmacie proche du bien'],
            ['id' => 4, 'name' => 'Transport', 'description' => 'Arrêt de transport accessible'],
        ];

        foreach ($items as $item) {
            $this->upsertIfTableExists('prossimite_proprietes', ['agence_id' => $agenceId, 'name' => $item['name']], [
                'id' => $item['id'],
                'agence_id' => $agenceId,
                'name' => $item['name'],
                'description' => $item['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedProprietaires(string $agenceId): void
    {
        $suffix = substr(str_replace('-', '', $agenceId), 0, 8);
        $proprietaires = [
            [
                'proprietaire_id' => "seed-proprietaire-{$suffix}-1",
                'code' => "PR-{$suffix}-0001",
                'name' => 'Kouadio Yao',
                'tel1' => "070{$suffix}1",
                'tel2' => null,
                'type_piece_id' => 1,
                'numpiece' => "CI-{$suffix}-000001",
                'email' => "kouadio.yao+{$suffix}@example.com",
                'profession' => 'Commerçant',
                'nationalite' => 'Ivoirienne',
                'date_naiss' => '1984-03-12',
                'lieu_naiss' => 'Abidjan',
                'region_id' => 1,
                'ville_id' => 1,
                'adresse' => 'Cocody Angré',
                'genre_id' => 1,
                'date_expiration_piece' => '2030-03-12',
                'password' => Hash::make('123456789'),
            ],
            [
                'proprietaire_id' => "seed-proprietaire-{$suffix}-2",
                'code' => "PR-{$suffix}-0002",
                'name' => 'Nguessan Awa',
                'tel1' => "071{$suffix}2",
                'tel2' => null,
                'type_piece_id' => 1,
                'numpiece' => "CI-{$suffix}-000002",
                'email' => "nguessan.awa+{$suffix}@example.com",
                'profession' => 'Gestionnaire',
                'nationalite' => 'Ivoirienne',
                'date_naiss' => '1990-07-28',
                'lieu_naiss' => 'Yamoussoukro',
                'region_id' => 1,
                'ville_id' => 2,
                'adresse' => 'Marcory Zone 4',
                'genre_id' => 2,
                'date_expiration_piece' => '2031-07-28',
                'password' => Hash::make('123456789'),
            ],
        ];

        foreach ($proprietaires as $proprietaire) {
            $this->upsertIfTableExists('proprietaires', ['numpiece' => $proprietaire['numpiece']], [
                'proprietaire_id' => $proprietaire['proprietaire_id'],
                'code' => $proprietaire['code'],
                'name' => $proprietaire['name'],
                'tel1' => $proprietaire['tel1'],
                'tel2' => $proprietaire['tel2'],
                'type_piece_id' => $proprietaire['type_piece_id'],
                'type_pieces_id' => $proprietaire['type_piece_id'],
                'numpiece' => $proprietaire['numpiece'],
                'email' => $proprietaire['email'],
                'profession' => $proprietaire['profession'],
                'nationalite' => $proprietaire['nationalite'],
                'date_naiss' => $proprietaire['date_naiss'],
                'lieu_naiss' => $proprietaire['lieu_naiss'],
                'region_id' => $proprietaire['region_id'],
                'ville_id' => $proprietaire['ville_id'],
                'adresse' => $proprietaire['adresse'],
                'genre_id' => $proprietaire['genre_id'],
                'date_expiration_piece' => $proprietaire['date_expiration_piece'],
                'password' => $proprietaire['password'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->upsertIfTableExists('proprietaire_agences', ['proprietaire_id' => $proprietaire['proprietaire_id'], 'agence_id' => $agenceId], [
                'proprietaire_agence_id' => "seed-proprietaire-agence-{$suffix}-{$proprietaire['numpiece']}",
                'proprietaire_id' => $proprietaire['proprietaire_id'],
                'agence_id' => $agenceId,
                'is_active' => true,
                'date_activation' => now(),
                'name_representant' => null,
                'adresse_representant' => null,
                'tel1_representant' => null,
                'tel2_representant' => null,
                'email_representant' => null,
                'created_by' => $this->adminId,
                'updated_by' => $this->adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedLots(string $agenceId): void
    {
        $suffix = substr(str_replace('-', '', $agenceId), 0, 8);
        $lots = [
            [
                'propreietaire_lot_id' => "seed-lot-{$suffix}-1",
                'lot_id' => "seed-lot-id-{$suffix}-1",
                'name' => 'Lot Riviera 1',
                'superficie' => 450.00,
                'region_id' => 1,
                'ville_id' => 1,
                'adresse' => 'Riviera Bonoumin',
                'num_lot' => 'L-001',
                'num_ilot' => 'I-01',
                'proprietaire_id' => "seed-proprietaire-{$suffix}-1",
            ],
            [
                'propreietaire_lot_id' => "seed-lot-{$suffix}-2",
                'lot_id' => "seed-lot-id-{$suffix}-2",
                'name' => 'Lot Zone 4',
                'superficie' => 320.00,
                'region_id' => 1,
                'ville_id' => 2,
                'adresse' => 'Marcory Zone 4',
                'num_lot' => 'L-002',
                'num_ilot' => 'I-02',
                'proprietaire_id' => "seed-proprietaire-{$suffix}-2",
            ],
        ];

        foreach ($lots as $lot) {
            $this->upsertIfTableExists('propietaire_lots', ['propreietaire_lot_id' => $lot['propreietaire_lot_id']], [
                'propreietaire_lot_id' => $lot['propreietaire_lot_id'],
                'lot_id' => $lot['lot_id'],
                'name' => $lot['name'],
                'superficie' => $lot['superficie'],
                'region_id' => $lot['region_id'],
                'ville_id' => $lot['ville_id'],
                'adresse' => $lot['adresse'],
                'num_lot' => $lot['num_lot'],
                'num_ilot' => $lot['num_ilot'],
                'proprietaire_id' => $lot['proprietaire_id'],
                'agence_id' => $agenceId,
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]); 
        }
    }

    private function seedProperties(string $agenceId): void
    {
        $suffix = substr(str_replace('-', '', $agenceId), 0, 8);

        $property = [
            'propriete_id' => "seed-propriete-{$suffix}-1",
            'reference' => "PROP-{$suffix}-0001",
            'description' => 'Résidence de test avec deux bâtiments et des portes déjà tarifées.',
            'agence_id' => $agenceId,
            'lot_id' => "seed-lot-{$suffix}-1",
            'proprietaire_id' => "seed-proprietaire-{$suffix}-1",
            'type_propriete_id' => 1,
            'adresse_complete' => 'Riviera Bonoumin, Abidjan',
            'videos_url' => null,
            'is_allocation' => true,
            'is_actif' => true,
            'prossimites' => json_encode(['1', '3', '4']),
            'created_by' => $this->agentId,
            'updated_by' => $this->agentId,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];

        $this->upsertIfTableExists('propriete', ['propriete_id' => $property['propriete_id']], $property);

        $batiments = [
            [
                'batiment_id' => "seed-batiment-{$suffix}-1",
                'propriete_id' => $property['propriete_id'],
                'agence_id' => $agenceId,
                'name' => 'Bloc A',
                'description' => 'Bâtiment principal de la résidence.',
                'nbre_etages' => 3,
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'batiment_id' => "seed-batiment-{$suffix}-2",
                'propriete_id' => $property['propriete_id'],
                'agence_id' => $agenceId,
                'name' => 'Bloc B',
                'description' => 'Second bâtiment avec studios.',
                'nbre_etages' => 2,
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($batiments as $batiment) {
            $this->upsertIfTableExists('batiment', ['batiment_id' => $batiment['batiment_id']], $batiment);
        }

        $portes = [
            [
                'porte_id' => "seed-porte-{$suffix}-1",
                'batiment_id' => "seed-batiment-{$suffix}-1",
                'type_porte_id' => 1,
                'numero_porte' => 'A-101',
                'agence_id' => $agenceId,
                'superficie_m2' => 72.50,
                'etage' => 1,
                'description' => 'Appartement trois pièces côté cour.',
                'is_occupe' => false,
                'is_actif' => true,
                'caution' => 1,
                'avance' => 1,
                'agence' => 1,
                'mt_caution_cie' => 5000,
                'mt_caution_sodeci' => 5000,
                'mt_autre_frais' => 0,
                'mt_loyer' => 180000,
                'equipements' => json_encode(['1', '2']),
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'porte_id' => "seed-porte-{$suffix}-2",
                'batiment_id' => "seed-batiment-{$suffix}-1",
                'type_porte_id' => 2,
                'numero_porte' => 'A-102',
                'agence_id' => $agenceId,
                'superficie_m2' => 64.00,
                'etage' => 1,
                'description' => 'Appartement deux pièces avec balcon.',
                'is_occupe' => true,
                'is_actif' => true,
                'caution' => 1,
                'avance' => 1,
                'agence' => 1,
                'mt_caution_cie' => 5000,
                'mt_caution_sodeci' => 5000,
                'mt_autre_frais' => 0,
                'mt_loyer' => 165000,
                'equipements' => json_encode(['1']),
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'porte_id' => "seed-porte-{$suffix}-3",
                'batiment_id' => "seed-batiment-{$suffix}-2",
                'type_porte_id' => 3,
                'numero_porte' => 'B-201',
                'agence_id' => $agenceId,
                'superficie_m2' => 38.75,
                'etage' => 2,
                'description' => 'Studio meublable au dernier niveau.',
                'is_occupe' => false,
                'is_actif' => true,
                'caution' => 1,
                'avance' => 1,
                'agence' => 1,
                'mt_caution_cie' => 3000,
                'mt_caution_sodeci' => 3000,
                'mt_autre_frais' => 0,
                'mt_loyer' => 120000,
                'equipements' => json_encode(['2', '3']),
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($portes as $porte) {
            $this->upsertIfTableExists('porte', ['porte_id' => $porte['porte_id']], $porte);

            $tarif = [
                'tarif_id' => "seed-tarif-{$porte['porte_id']}",
                'porte_id' => $porte['porte_id'],
                'mt_loyer' => $porte['mt_loyer'],
                'mt_caution' => 0,
                'mt_avance' => 0,
                'mt_frais_agence' => 0,
                'mt_caution_cie' => $porte['mt_caution_cie'],
                'mt_caution_sodeci' => $porte['mt_caution_sodeci'],
                'date_effet' => now()->toDateString(),
                'is_actif' => true,
                'created_by' => $this->agentId,
                'updated_by' => $this->agentId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->upsertIfTableExists('tarif_porte', ['tarif_id' => $tarif['tarif_id']], $tarif);
        }
    }

    private function getAgenceIds(): array
    {
        if (! Schema::hasTable('agences')) {
            return [$this->agenceId];
        }

        $agenceIds = DB::table('agences')->pluck('agence_id')->filter()->values()->all();

        return $agenceIds ?: [$this->agenceId];
    }

    private function upsertIfTableExists(string $table, array $match, array $values): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = Schema::getColumnListing($table);
        $allowed = array_flip($columns);

        $match = array_intersect_key($match, $allowed);
        $values = array_intersect_key($values, $allowed);

        if (empty($match)) {
            return;
        }

        DB::table($table)->updateOrInsert($match, $values);
    }
}
