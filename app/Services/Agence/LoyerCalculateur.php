<?php

namespace App\Services\Agence;

use App\Models\ParametrageAgence;
use App\Models\Porte;

/**
 * Centralise les règles de calcul utilisées à la fois par la génération
 * mensuelle des loyers (GenererLoyersMensuels) et par le paiement anticipé
 * de mois futurs (PaiementLoyerService), pour garantir que le montant du
 * loyer et sa répartition sont toujours calculés de la même façon.
 */
class LoyerCalculateur
{
    /**
     * Montant du loyer mensuel, lu sur la porte (source de vérité), plus
     * les charges récurrentes éventuelles (mt_autre_frais).
     */
    public function montantMensuel(Porte $porte): float
    {
        $montant = (float) $porte->mt_loyer;

        if ($porte->mt_autre_frais) {
            $montant += (float) $porte->mt_autre_frais;
        }

        return round($montant, 2);
    }

    /**
     * Répartit un montant entre propriétaire et agence selon le taux de
     * commission défini par l'agence (`commission`, en pourcentage).
     */
    public function repartition(float $montantAPayer, ?ParametrageAgence $param): array
    {
        $tauxCommission = (float) ($param->commission ?? 0);

        $montantAgence = round($montantAPayer * ($tauxCommission / 100), 2);
        $montantProprio = round($montantAPayer - $montantAgence, 2);

        return [$montantProprio, $montantAgence];
    }
}