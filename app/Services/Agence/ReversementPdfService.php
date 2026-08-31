<?php

namespace App\Services\Agence;

use App\Models\Reversement;
use App\Models\TransactionAgence;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReversementPdfService
{
    public function __construct(
        private readonly AgencyDocumentBranding $branding,
        private readonly AgencyDocumentApproval $approval,
    )
    {
    }

    public function download(Reversement $reversement): Response
    {
        $reversement->loadMissing([
            'agence.parametrage',
            'proprietaire',
            'lot',
            'vente.acheteur',
            'details.locataire',
            'details.porte',
        ]);

        $filename = 'fiche-reversement-'.optional($reversement->date_reversement)->format('Y-m-d')
            .'-'.substr($reversement->getKey(), 0, 8).'.pdf';

        $documentLogo = $this->branding->logoDataUri($reversement->agence);
        $documentApproval = $this->approval->data(
            $reversement->agence,
            'document-financier',
            (float) $reversement->net_a_reverser
        );

        if ($documentApproval['cachet']) {
            $documentApproval['cachet'] = 'data:image/png;base64,'.base64_encode(file_get_contents($documentApproval['cachet']));
        }
        foreach ($documentApproval['signatures'] as &$signature) {
            if ($signature['image']) {
                $signature['image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($signature['image']));
            }
        }
        unset($signature);

        $salePayments = collect();
        if ($reversement->type_reversement === 'vente' && $reversement->vente) {
            $paidBefore = (float) TransactionAgence::query()
                ->where('agence_id', $reversement->agence_id)
                ->where('type_transaction', TransactionAgence::STATUT_VENTE)
                ->where('reference', $reversement->vente_id)
                ->where('date_transaction', '<', $reversement->periode_debut->copy()->startOfDay())
                ->sum('montant_global_verser');

            $salePayments = TransactionAgence::query()
                ->where('agence_id', $reversement->agence_id)
                ->where('type_transaction', TransactionAgence::STATUT_VENTE)
                ->where('reference', $reversement->vente_id)
                ->whereBetween('date_transaction', [
                    $reversement->periode_debut->copy()->startOfDay(),
                    $reversement->periode_fin->copy()->endOfDay(),
                ])
                ->orderBy('date_transaction')
                ->get()
                ->map(function ($transaction) use (&$paidBefore, $reversement) {
                    $paidBefore += (float) $transaction->montant_global_verser;
                    return [
                        'date' => $transaction->date_transaction,
                        'receipt' => $transaction->numero_recu,
                        'amount' => (float) $transaction->montant_global_verser,
                        'remaining' => max((float) $reversement->vente->prix_vente - $paidBefore, 0),
                    ];
                });
        }

        return Pdf::loadView('agence.reversement.pdf', compact('reversement', 'documentLogo', 'documentApproval', 'salePayments'))
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
