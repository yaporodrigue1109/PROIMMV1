<?php

namespace App\Services;

use App\Models\Agence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AgenceDemoDataService
{
    public function seed(Agence $agence, string $actorId): void
    {
        if ($agence->statut !== 'en_demo' || $agence->abonnement_id) {
            return;
        }

        $suffix = substr(str_replace('-', '', $agence->agence_id), 0, 8);
        $id = fn (string $type, int $number = 1) => "demo-{$type}-{$suffix}-{$number}";
        $now = now();

        $this->upsert('proprietaires', ['proprietaire_id' => $id('proprietaire')], [
            'proprietaire_id' => $id('proprietaire'), 'code' => "DEMO-PR-{$suffix}",
            'name' => 'Kouadio Yao (Démo)', 'tel1' => '0700000001', 'email' => "demo.proprietaire.{$suffix}@example.test",
            'genre_id' => 1, 'type_pieces_id' => 1, 'type_proprietaire' => 'particulier',
            'numpiece' => "DEMO-CNI-{$suffix}", 'date_expiration_piece' => $now->copy()->addYears(5)->toDateString(),
            'adresse' => 'Cocody Angré', 'profession' => 'Entrepreneur', 'nationalite' => 'Ivoirienne',
            'password' => Hash::make('demo-password'), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->upsert('proprietaire_agences', ['proprietaire_agence_id' => $id('proprietaire-agence')], [
            'proprietaire_agence_id' => $id('proprietaire-agence'), 'proprietaire_id' => $id('proprietaire'),
            'agence_id' => $agence->agence_id, 'is_active' => true, 'date_activation' => $now,
            'created_by' => $actorId, 'updated_by' => $actorId, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->upsert('propietaire_lots', ['propreietaire_lot_id' => $id('lot')], [
            'propreietaire_lot_id' => $id('lot'), 'name' => 'Lot Riviera Démo', 'superficie' => 450,
            'adresse' => 'Riviera Bonoumin', 'num_lot' => 'D-001', 'num_ilot' => 'D-01',
            'proprietaire_id' => $id('proprietaire'), 'agence_id' => $agence->agence_id,
            'is_for_sale' => false, 'created_by' => $actorId, 'updated_by' => $actorId,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->upsert('propietaire_lots', ['propreietaire_lot_id' => $id('lot', 2)], [
            'propreietaire_lot_id' => $id('lot', 2), 'name' => 'Terrain Bingerville Démo', 'superficie' => 600,
            'adresse' => 'Bingerville', 'num_lot' => 'D-002', 'num_ilot' => 'D-02',
            'proprietaire_id' => $id('proprietaire'), 'agence_id' => $agence->agence_id,
            'is_for_sale' => true, 'sale_price' => 25000000, 'created_by' => $actorId, 'updated_by' => $actorId,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        if (Schema::hasTable('type_proprietes') && ! DB::table('type_proprietes')->where('agence_id', $agence->agence_id)->exists()) {
            $this->upsert('type_proprietes', ['agence_id' => $agence->agence_id, 'name' => 'Appartement'], [
                'agence_id' => $agence->agence_id, 'name' => 'Appartement',
                'description' => 'Type fictif de démonstration', 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $typeId = DB::table('type_proprietes')->where('agence_id', $agence->agence_id)->value('id')
            ?? DB::table('type_proprietes')->value('id');
        $this->upsert('propriete', ['propriete_id' => $id('propriete')], [
            'propriete_id' => $id('propriete'), 'proprietaire_id' => $id('proprietaire'),
            'agence_id' => $agence->agence_id, 'lot_id' => $id('lot'), 'type_propriete_id' => $typeId,
            'reference' => "DEMO-PROP-{$suffix}", 'description' => 'Résidence fictive de démonstration.',
            'adresse_complete' => 'Riviera Bonoumin, Abidjan', 'is_allocation' => true,
            'sale_type' => 'none', 'is_actif' => true, 'created_by' => $actorId, 'updated_by' => $actorId,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->upsert('batiment', ['batiment_id' => $id('batiment')], [
            'batiment_id' => $id('batiment'), 'propriete_id' => $id('propriete'), 'agence_id' => $agence->agence_id,
            'name' => 'Bâtiment Démo', 'description' => 'Bâtiment fictif', 'nbre_etages' => 2,
            'created_by' => $actorId, 'updated_by' => $actorId, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $doorTypeId = DB::table('type_porte')->value('type_porte_id');
        foreach ([1 => ['A-01', 150000, true], 2 => ['A-02', 125000, false], 3 => ['A-03', 100000, false]] as $number => [$label, $rent, $occupied]) {
            $this->upsert('porte', ['porte_id' => $id('porte', $number)], [
                'porte_id' => $id('porte', $number), 'batiment_id' => $id('batiment'), 'type_porte_id' => $doorTypeId,
                'agence_id' => $agence->agence_id, 'numero_porte' => $label, 'superficie_m2' => 55 + $number,
                'etage' => $number > 2 ? 1 : 0, 'is_allocation' => true, 'is_occupe' => $occupied,
                'is_actif' => true, 'mt_loyer' => $rent, 'created_by' => $actorId, 'updated_by' => $actorId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->upsert('tarif_porte', ['tarif_id' => $id('tarif', $number)], [
                'tarif_id' => $id('tarif', $number), 'porte_id' => $id('porte', $number), 'mt_loyer' => $rent,
                'mt_caution' => 1, 'mt_avance' => 1, 'mt_frais_agence' => 1, 'date_effet' => $now->toDateString(),
                'is_actif' => true, 'created_by' => $actorId, 'updated_by' => $actorId, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $this->upsert('locataire', ['locataire_id' => $id('locataire')], [
            'locataire_id' => $id('locataire'), 'name' => 'Aminata Koné (Démo)', 'code' => "DEMO-LOC-{$suffix}",
            'tel1' => '0500000001', 'email' => "demo.locataire.{$suffix}@example.test", 'adresse' => 'Cocody',
            'type_piece_id' => 1, 'num_piece' => "DEMO-LOC-CNI-{$suffix}", 'genre_id' => 2,
            'profession' => 'Comptable', 'password' => Hash::make('demo-password'), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->upsert('locataire_agence', ['locataire_agence_id' => $id('contrat')], [
            'locataire_agence_id' => $id('contrat'), 'agence_id' => $agence->agence_id,
            'locataire_id' => $id('locataire'), 'proprietaire_id' => $id('proprietaire'),
            'propriete_id' => $id('propriete'), 'batiment_id' => $id('batiment'), 'lot_id' => $id('lot'),
            'porte_id' => $id('porte'), 'loyer_net' => 150000, 'date_debut_bail' => $now->copy()->subMonths(2),
            'date_entree' => $now->copy()->subMonths(2), 'is_active' => true, 'is_new' => false,
            'created_by' => $actorId, 'updated_by' => $actorId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->upsert('loyer', ['loyer_id' => $id('loyer')], [
            'loyer_id' => $id('loyer'), 'locataire_id' => $id('locataire'), 'proprietaire_id' => $id('proprietaire'),
            'agence_id' => $agence->agence_id, 'propriete_id' => $id('propriete'), 'batiment_id' => $id('batiment'),
            'lot_id' => $id('lot'), 'porte_id' => $id('porte'), 'statut' => 'Paiement total',
            'montant_a_payer' => 150000, 'montant_payer' => 150000, 'montant_restant' => 0,
            'mois_paiement' => $now->month, 'annee_paiement' => $now->year, 'date_paiement' => $now->copy()->subDays(4),
            'date_limit_paiement' => $now->copy()->endOfMonth(), 'created_by' => $actorId,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->transaction($id('transaction-loyer'), $agence->agence_id, $actorId, 'loyer', 150000, $now->copy()->subDays(4), $id);
        $this->transaction($id('transaction-vente'), $agence->agence_id, $actorId, 'vente', 8500000, $now->copy()->subDays(9), $id);

        $this->upsert('maintenance', ['maintenance_id' => $id('maintenance')], [
            'maintenance_id' => $id('maintenance'), 'agence_id' => $agence->agence_id,
            'proprietaire_id' => $id('proprietaire'), 'lot_id' => $id('lot'), 'propriete_id' => $id('propriete'),
            'batiment_id' => $id('batiment'), 'porte_id' => $id('porte', 2), 'titre' => 'Réparation plomberie (Démo)',
            'description' => 'Intervention fictive pour découvrir le module.', 'statut' => 'en cours',
            'montant_global' => 75000, 'prise_en_charge_par' => 'agence', 'created_by' => $actorId,
            'updated_by' => $actorId, 'created_at' => $now->copy()->subDays(2), 'updated_at' => $now,
        ]);
    }

    public function purge(Agence $agence): void
    {
        $suffix = substr(str_replace('-', '', $agence->agence_id), 0, 8);
        foreach (['transaction_agences', 'loyer', 'locataire_agence', 'maintenance', 'tarif_porte', 'porte', 'batiment', 'propriete', 'propietaire_lots', 'proprietaire_agences'] as $table) {
            if (!Schema::hasTable($table)) continue;
            $primary = Schema::getColumnListing($table)[0] ?? 'id';
            DB::table($table)->where($primary, 'like', "demo-%-{$suffix}-%")->delete();
        }
        if (Schema::hasTable('locataire')) DB::table('locataire')->where('locataire_id', 'like', "demo-locataire-{$suffix}-%")->delete();
        if (Schema::hasTable('proprietaires')) DB::table('proprietaires')->where('proprietaire_id', 'like', "demo-proprietaire-{$suffix}-%")->delete();
    }

    private function transaction(string $transactionId, string $agenceId, string $actorId, string $type, float $amount, $date, \Closure $id): void
    {
        $this->upsert('transaction_agences', ['transaction_agence_id' => $transactionId], [
            'transaction_agence_id' => $transactionId, 'agence_id' => $agenceId,
            'locataire_id' => $id('locataire'), 'loyer_id' => $type === 'loyer' ? $id('loyer') : null,
            'proprietaire_id' => $id('proprietaire'), 'propriete_id' => $id('propriete'),
            'batiment_id' => $id('batiment'), 'porte_id' => $id('porte'), 'montant_global_verser' => $amount,
            'montant_loyer_payer' => $type === 'loyer' ? $amount : 0, 'type_transaction' => $type,
            'is_reversement' => false, 'date_transaction' => $date, 'created_by' => $actorId,
            'updated_by' => $actorId, 'created_at' => $date, 'updated_at' => $date,
        ]);
    }

    private function upsert(string $table, array $match, array $values): void
    {
        if (!Schema::hasTable($table)) return;
        $allowed = array_flip(Schema::getColumnListing($table));
        $match = array_intersect_key($match, $allowed);
        $values = array_intersect_key($values, $allowed);
        if ($match) DB::table($table)->updateOrInsert($match, $values);
    }
}
