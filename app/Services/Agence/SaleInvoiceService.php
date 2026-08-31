<?php

namespace App\Services\Agence;

use App\Models\TransactionAgence;
use App\Models\VenteBien;

class SaleInvoiceService
{
    public function __construct(private AgencyDocumentBranding $branding, private AgencyDocumentApproval $approval) {}

    public function generate(TransactionAgence $transaction): string
    {
        $transaction->loadMissing(['agence.parametrage', 'modePaiement']);
        $sale = VenteBien::with(['acheteur', 'proprietaire', 'lot', 'propriete.lot', 'porte'])->findOrFail($transaction->reference);
        $agency = $transaction->agence;
        $payments = TransactionAgence::withoutGlobalScopes()
            ->where('agence_id', $transaction->agence_id)
            ->where('type_transaction', TransactionAgence::STATUT_VENTE)
            ->where('reference', (string) $sale->getKey())
            ->orderBy('created_at')
            ->orderBy('transaction_agence_id')
            ->get()
            ->values();
        $paymentIndex = $payments->search(fn ($payment) => (string) $payment->getKey() === (string) $transaction->getKey());
        $paymentRank = $paymentIndex === false ? 1 : $paymentIndex + 1;
        $paidAtThisReceipt = (float) $payments->take($paymentRank)->sum('montant_global_verser');
        $remaining = max((float) $sale->prix_vente - $paidAtThisReceipt, 0);
        $lot = $sale->lot?->name ?? $sale->propriete?->lot?->name;
        $assetParts = array_filter([
            $lot ? 'Lot '.$lot : null,
            $sale->propriete?->reference ? 'Propriete '.$sale->propriete->reference : null,
            $sale->porte?->numero_porte ? 'Porte '.$sale->porte->numero_porte : null,
            $sale->propriete?->adresse_complete,
        ]);
        $asset = $assetParts ? implode(' - ', $assetParts) : 'Bien immobilier';

        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(12, 10, 12); $pdf->SetAutoPageBreak(false); $pdf->AddPage();
        $blue = [16, 55, 112]; $red = [225, 45, 45];
        $pdf->SetDrawColor(190, 205, 225); $pdf->Rect(9, 9, 279, 192);
        if ($watermark = $this->branding->watermarkPath($agency)) { try { $pdf->Image($watermark, 102, 52, 92, 92); } catch (\Throwable) {} }
        if ($logo = $this->branding->localLogoPath($agency)) { try { $pdf->Image($logo, 16, 15, 22, 22); } catch (\Throwable) {} }

        // En-tete : chaque bloc possède sa propre zone pour éviter tout chevauchement.
        $pdf->SetTextColor(...$blue); $pdf->SetXY(44, 15); $pdf->SetFont('Arial', 'B', 13); $pdf->Cell(105, 6, $this->text($agency?->name ?: 'Agence immobiliere'));
        $pdf->SetXY(44, 22); $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell(105, 4.3, $this->text(implode("\n", array_filter([
            $agency?->adresse,
            implode(' / ', array_filter([$agency?->tel1, $agency?->tel2])),
            implode(' / ', array_filter([$agency?->email1, $agency?->email2])),
        ]))));
        $pdf->SetXY(158, 16); $pdf->SetFont('Times', 'B', 19); $pdf->Cell(122, 8, $this->text('QUITTANCE DE VERSEMENT'), 0, 1, 'R');
        $pdf->SetXY(158, 27); $pdf->SetFont('Arial', 'B', 10); $pdf->SetTextColor(...$red);
        $pdf->Cell(122, 6, $this->text('N° '.$transaction->numero_recu), 0, 1, 'R');
        $pdf->SetXY(158, 35); $pdf->SetTextColor(...$blue); $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(122, 5, $this->text('Versement n° '.$paymentRank.' - '.optional($transaction->date_transaction)->format('d/m/Y')), 0, 1, 'R');
        $pdf->Line(16, 46, 280, 46);

        $this->field($pdf, 16, 53, 'Vendeur / proprietaire', $sale->proprietaire?->name ?: 'Non renseigne');
        $this->field($pdf, 16, 64, 'Acheteur', trim(($sale->acheteur?->name ?: 'Non renseigne').' - '.($sale->acheteur?->telephone1 ?: '')));
        $this->field($pdf, 16, 75, 'Bien concerne', $asset);
        $this->field($pdf, 16, 86, 'Date de l’accord', optional($sale->date_accord)->format('d/m/Y'));
        $this->field($pdf, 16, 97, 'Mode de reglement', $transaction->modePaiement?->name ?: 'Non renseigne');

        $pdf->SetFillColor(244, 248, 252); $pdf->SetDrawColor(190, 205, 225);
        $this->amountBox($pdf, 16, 112, 61, 'Prix initial du bien', (float) $sale->prix_vente, $blue);
        $this->amountBox($pdf, 82, 112, 61, 'Versement actuel (n° '.$paymentRank.')', (float) $transaction->montant_global_verser, [36, 91, 0]);
        $this->amountBox($pdf, 148, 112, 61, 'Cumul verse', $paidAtThisReceipt, $blue);
        $this->amountBox($pdf, 214, 112, 66, 'Reste a verser', $remaining, $red);

        $pdf->SetXY(16, 139); $pdf->SetFillColor(234, 244, 251); $pdf->SetFont('Arial', 'B', 11); $pdf->SetTextColor(...$blue);
        $pdf->MultiCell(264, 7, $this->text('Arrete la presente quittance a la somme de : '.$this->words((float) $transaction->montant_global_verser).' francs CFA.'), 1, 'C', true);
        $approval = $this->approval->data($agency, 'vente', (float) $transaction->montant_global_verser);
        $signatures = collect($approval['signatures'])->sortBy(fn (array $signature) =>
            str_contains(mb_strtolower((string) $signature['title']), 'comptable') ? 0 : 1
        )->values();
        foreach ($signatures as $index => $signature) {
            if ($signature['image']) { try { $pdf->Image($signature['image'], 180 + ($index * 48), 162, 38, 14); } catch (\Throwable) {} }
        }
        if ($approval['cachet']) { try { $pdf->Image($approval['cachet'], 248, 159, 28); } catch (\Throwable) {} }
        $pdf->SetTextColor(...$blue); $pdf->SetXY(176, 181); $pdf->SetFont('Arial', '', 8); $pdf->Cell(48, 5, 'Responsable Comptable', 0, 0, 'C'); $pdf->Cell(48, 5, 'Direction Generale', 0, 0, 'C');
        return $pdf->Output('S');
    }

    private function field(\FPDF $pdf, float $x, float $y, string $label, string $value): void
    {
        $pdf->SetXY($x, $y); $pdf->SetFont('Arial', '', 10); $w = 36; $pdf->Cell($w, 7, $this->text($label)); $pdf->Cell(228, 7, $this->text($value), 'B');
    }

    private function amountBox(\FPDF $pdf, float $x, float $y, float $width, string $label, float $amount, array $color): void
    {
        $pdf->SetXY($x, $y); $pdf->SetFillColor(244, 248, 252); $pdf->SetDrawColor(190, 205, 225);
        $pdf->SetTextColor(95, 113, 130); $pdf->SetFont('Arial', '', 8); $pdf->Cell($width, 8, $this->text($label), 'LTR', 2, 'C', true);
        $pdf->SetTextColor(...$color); $pdf->SetFont('Arial', 'B', 12); $pdf->Cell($width, 11, $this->text(number_format($amount, 0, ',', ' ').' FCFA'), 'LBR', 0, 'C', true);
    }

    private function words(float $amount): string
    {
        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::SPELLOUT);
            $words = $formatter->format((int) round($amount));
            if ($words !== false) {
                return ucfirst($words);
            }
        }

        return number_format($amount, 0, ',', ' ');
    }
    private function text(mixed $value): string { return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $value) ?: (string) $value; }
}
