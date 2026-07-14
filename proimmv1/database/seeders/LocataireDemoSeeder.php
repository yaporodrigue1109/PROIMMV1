<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class LocataireDemoSeeder extends Seeder
{
    private string $agenceId = '22222222-2222-2222-2222-222222222222';
    private string $locataireId = '55555555-5555-5555-5555-555555555555';
    private string $contratId = '66666666-6666-6666-6666-666666666666';
    private string $loyerId = '77777777-7777-7777-7777-777777777777';
    private string $transactionAgenceId = '88888888-8888-8888-8888-888888888888';

    public function run(): void
    {
        $now = now();
        $porteId = 'seed-porte-22222222-1';
        $batimentId = 'seed-batiment-22222222-1';
        $proprieteId = 'seed-propriete-22222222-1';
        $proprietaireId = 'seed-proprietaire-22222222-1';
        $lotId = 'seed-lot-22222222-1';

        $this->upsertIfTableExists('locataire', ['locataire_id' => $this->locataireId], [
            'locataire_id' => $this->locataireId,
            'name' => 'Aminata Koné',
            'code' => 'LOC-2222-0001',
            'tel1' => '0700001111',
            'tel2' => '0102030405',
            'email' => 'aminata.kone@example.com',
            'region_id' => 1,
            'ville_id' => 1,
            'adresse' => 'Riviera Bonoumin, Abidjan',
            'nationalite' => 'Ivoirienne',
            'type_piece_id' => 1,
            'num_piece' => 'CI-ABJ-000123',
            'date_expiration_piece' => '2030-06-30',
            'date_naissance' => '1992-04-18',
            'lieu_naissance' => 'Abidjan',
            'genre_id' => 2,
            'photo' => null,
            'image_pice' => null,
            'profession' => 'Assistante de direction',
            'password' => Hash::make('123456789'),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $this->upsertIfTableExists('locataire_agence', ['locataire_agence_id' => $this->contratId], [
            'locataire_agence_id' => $this->contratId,
            'agence_id' => $this->agenceId,
            'locataire_id' => $this->locataireId,
            'proprietaire_id' => $proprietaireId,
            'propriete_id' => $proprieteId,
            'batiment_id' => $batimentId,
            'lot_id' => $lotId,
            'porte_id' => $porteId,
            'nbre_personne' => 3,
            'nbre_caution' => 1,
            'nbre_avance' => 1,
            'nbre_agence' => 1,
            'is_active' => true,
            'is_new' => true,
            'civilite_representant_id' => null,
            'name_representant' => 'Aminata Koné',
            'adresse_representant' => 'Riviera Bonoumin, Abidjan',
            'contant_representant' => '0700001111',
            'nbre_enfant' => 1,
            'date_debut_bail' => '2026-07-01',
            'date_fin_bail' => '2027-06-30',
            'date_entree' => '2026-07-08',
            'date_signature_bail' => '2026-07-08',
            'periodicite_paiement_id' => 3,
            'pas_de_porte' => 25000,
            'montant_global_garantie' => 620000,
            'versements_depot_garantie' => json_encode([
                [
                    'montant' => 250000,
                    'date_versement' => '2026-07-08',
                    'mode_paiement_id' => 3,
                ],
                [
                    'montant' => 370000,
                    'date_versement' => '2026-07-09',
                    'mode_paiement_id' => 1,
                ],
            ]),
            'created_by' => $this->agenceId,
            'updated_by' => $this->agenceId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $this->upsertIfTableExists('loyer', ['loyer_id' => $this->loyerId], [
            'loyer_id' => $this->loyerId,
            'locataire_id' => $this->locataireId,
            'proprietaire_id' => $proprietaireId,
            'agence_id' => $this->agenceId,
            'propriete_id' => $proprieteId,
            'batiment_id' => $batimentId,
            'porte_id' => $porteId,
            'lot_id' => $lotId,
            'statut' => 'Paiement partiel',
            'montant_a_payer' => 180000,
            'montant_payer' => 120000,
            'montant_restant' => 60000,
            'montant_agence' => 18000,
            'montant_proprio' => 162000,
            'montant_global_proprio' => 162000,
            'montant_global_agence' => 18000,
            'mode_paiement_id' => 3,
            'arriere_precedent' => 0,
            'is_first' => true,
            'mois_paiement' => 7,
            'annee_paiement' => 2026,
            'date_paiement' => $now,
            'date_limit_paiement' => '2026-07-10',
            'commentaire' => 'Loyer de démonstration pour la page détail.',
            'creaeted_by' => $this->agenceId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $this->upsertIfTableExists('transaction_agences', ['transaction_agence_id' => $this->transactionAgenceId], [
            'transaction_agence_id' => $this->transactionAgenceId,
            'locataire_id' => $this->locataireId,
            'agence_id' => $this->agenceId,
            'proprietaire_id' => $proprietaireId,
            'propriete_id' => $proprieteId,
            'batiment_id' => $batimentId,
            'porte_id' => $porteId,
            'loyer_id' => $this->loyerId,
            'montant_global_verser' => 120000,
            'montant_total_verse' => 120000,
            'montant_loyer_payer' => 120000,
            'montant_arriere_paye' => 0,
            'montant_avance_payer' => 120000,
            'arriere_actuel' => 0,
            'arriere_restant' => 60000,
            'is_reversement' => false,
            'date_reversement' => null,
            'mois_payer' => json_encode(['Juil 2026']),
            'date_transaction' => $now,
            'mode_paiement_id' => 3,
            'reference_paiement' => 'PAY-DEMO-0001',
            'commentaire' => 'Paiement de démonstration.',
            'created_by' => $this->agenceId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    private function upsertIfTableExists(string $table, array $match, array $values): void
    {
        if (!Schema::hasTable($table)) {
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
