<?php

namespace App\Services\Agence;

use App\Models\Agence;
use App\Models\CaisseSession;
use App\Models\TransactionAgence;
use App\Models\Maintenance;
use App\Models\VenteBien;
use Carbon\Carbon;

class CashHistoryPdfService
{
    public function __construct(private AgencyDocumentBranding $branding) {}

    public function history(string $agenceId, array $filters = []): string
    {
        if (empty($filters['date_debut']) && empty($filters['date_fin'])) {
            $filters['date_debut'] = now()->startOfMonth()->toDateString();
            $filters['date_fin'] = now()->endOfMonth()->toDateString();
        }
        $dateDebut = ! empty($filters['date_debut']) ? Carbon::parse($filters['date_debut'])->startOfDay() : null;
        $dateFin = ! empty($filters['date_fin']) ? Carbon::parse($filters['date_fin'])->endOfDay() : null;
        $sessions = CaisseSession::where('agence_id', $agenceId)->latest('opened_at')->get()
            ->filter(function (CaisseSession $session) use ($dateDebut, $dateFin) {
                $start = ($session->closed_at && $session->opened_at->gt($session->closed_at) && $session->created_at) ? Carbon::parse($session->created_at) : $session->opened_at;
                return (! $dateDebut || $start->gte($dateDebut)) && (! $dateFin || $start->lte($dateFin));
            })->sortByDesc(function (CaisseSession $session) {
                return (($session->closed_at && $session->opened_at->gt($session->closed_at) && $session->created_at) ? Carbon::parse($session->created_at) : $session->opened_at)->timestamp;
            })->take(100);
        $pdf = $this->document($agenceId, 'HISTORIQUE DE CAISSE');
        $this->tableHeader($pdf, ['Ouverture', 'Fermeture', 'Initial', 'Entrees', 'Sorties', 'Theorique', 'Reel'], [35, 35, 26, 26, 26, 28, 26]);
        foreach ($sessions as $session) {
            [$start, $transactions] = $this->sessionTransactions($session);
            $entries = (float) $transactions->whereIn('type_transaction', ['loyer', 'vente'])->sum('montant_global_verser');
            $outputs = (float) $transactions->whereIn('type_transaction', ['maintenance', 'depense'])->sum('montant_global_verser');
            $this->row($pdf, [
                $start->format('d/m/Y H:i'), $session->closed_at?->format('d/m/Y H:i') ?: 'Ouverte',
                $this->money($session->solde_ouverture), $this->money($entries), $this->money($outputs),
                $this->money($session->solde_theorique ?? ((float) $session->solde_ouverture + $entries - $outputs)),
                $session->solde_fermeture === null ? '-' : $this->money($session->solde_fermeture),
            ], [35, 35, 26, 26, 26, 28, 26]);
        }
        return $pdf->Output('S');
    }

    public function session(CaisseSession $session): string
    {
        [$start, $transactions] = $this->sessionTransactions($session);
        $maintenanceById = Maintenance::withoutGlobalScopes()
            ->where('agence_id', $session->agence_id)
            ->whereIn('maintenance_id', $transactions->where('type_transaction', 'maintenance')->pluck('reference')->filter()->all())
            ->with(['proprietaire', 'lot', 'porte', 'details.maintenancier'])
            ->get()->keyBy(fn ($maintenance) => (string) $maintenance->getKey());
        $saleById = VenteBien::withoutGlobalScopes()
            ->where('agence_id', $session->agence_id)
            ->whereIn('id_vente', $transactions->where('type_transaction', 'vente')->pluck('reference')->filter()->all())
            ->with(['acheteur', 'proprietaire', 'lot', 'propriete.lot', 'porte'])
            ->get()->keyBy(fn ($sale) => (string) $sale->getKey());
        $pdf = $this->document($session->agence_id, 'RAPPORT D\'ACTIVITE DE CAISSE');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, $this->text('Periode : '.$start->format('d/m/Y H:i').' - '.($session->closed_at?->format('d/m/Y H:i') ?: 'En cours')), 0, 1);
        $pdf->Cell(0, 6, $this->text('Solde initial : '.$this->money($session->solde_ouverture).' FCFA'), 0, 1);
        $pdf->Ln(3);
        $widths = [34, 25, 45, 34, 60, 60];
        $this->tableHeader($pdf, ['Date', 'Type', 'N° recu', 'Mode', 'Entree', 'Sortie'], $widths);
        foreach ($transactions as $transaction) {
            $incoming = in_array($transaction->type_transaction, ['loyer', 'vente'], true);
            $detail = $this->transactionDetail(
                $transaction,
                $maintenanceById->get((string) $transaction->reference),
                $saleById->get((string) $transaction->reference)
            );
            $this->transactionBlock($pdf, [
                ($transaction->created_at ?: $transaction->date_transaction)?->format('d/m/Y H:i') ?: '-',
                ucfirst((string) $transaction->type_transaction), $transaction->numero_recu ?: (string) $transaction->getKey(),
                $transaction->modePaiement?->name ?: '-',
                $incoming ? $this->money($transaction->montant_global_verser) : '-',
                $incoming ? '-' : $this->money($transaction->montant_global_verser),
            ], $widths, $detail);
        }
        $entries = (float) $transactions->whereIn('type_transaction', ['loyer', 'vente'])->sum('montant_global_verser');
        $outputs = (float) $transactions->whereIn('type_transaction', ['maintenance', 'depense'])->sum('montant_global_verser');
        $pdf->Ln(4); $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, $this->text('TOTAL ENTREES : '.$this->money($entries).' FCFA    |    TOTAL SORTIES : '.$this->money($outputs).' FCFA'), 0, 1, 'R');
        $pdf->Cell(0, 7, $this->text('SOLDE THEORIQUE : '.$this->money($session->solde_theorique ?? ((float) $session->solde_ouverture + $entries - $outputs)).' FCFA    |    SOLDE REEL : '.($session->solde_fermeture === null ? '-' : $this->money($session->solde_fermeture).' FCFA')), 0, 1, 'R');
        return $pdf->Output('S');
    }

    private function sessionTransactions(CaisseSession $session): array
    {
        $start = ($session->closed_at && $session->opened_at->gt($session->closed_at) && $session->created_at) ? Carbon::parse($session->created_at) : $session->opened_at->copy();
        $end = $session->closed_at ?: now();
        $transactions = TransactionAgence::where('agence_id', $session->agence_id)->with(['modePaiement', 'locataire'])->get()->filter(function ($transaction) use ($start, $end) {
            $date = $transaction->created_at ?: $transaction->date_transaction;
            return $date && Carbon::parse($date)->between($start, $end, true);
        })->sortBy(fn ($transaction) => $transaction->created_at ?: $transaction->date_transaction)->values();
        return [$start, $transactions];
    }

    private function document(string $agenceId, string $title): \FPDF
    {
        $agency = Agence::find($agenceId); $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10); $pdf->SetAutoPageBreak(true, 14); $pdf->AddPage();
        if ($logo = $this->branding->localLogoPath($agency)) {
            try { $pdf->Image($logo, 10, 10, 24, 24); } catch (\Throwable) {}
        }
        $pdf->SetXY(39, 11); $pdf->SetTextColor(0, 85, 155); $pdf->SetFont('Arial', 'B', 14); $pdf->Cell(0, 7, $this->text($agency?->name ?: 'Agence immobiliere'), 0, 1);
        $pdf->SetX(39); $pdf->SetTextColor(30, 41, 59); $pdf->SetFont('Arial', '', 8); $pdf->Cell(0, 5, $this->text(implode(' | ', array_filter([$agency?->adresse, $agency?->tel1, $agency?->email1]))), 0, 1);
        $pdf->SetY(36);
        $pdf->Ln(3); $pdf->SetFont('Arial', 'B', 16); $pdf->Cell(0, 9, $this->text($title), 0, 1, 'C'); $pdf->Ln(3);
        return $pdf;
    }
    private function tableHeader(\FPDF $pdf, array $labels, array $widths): void { $pdf->SetFillColor(0, 85, 155); $pdf->SetTextColor(255); $pdf->SetFont('Arial', 'B', 8); foreach ($labels as $i => $label) $pdf->Cell($widths[$i], 8, $this->text($label), 1, 0, 'C', true); $pdf->Ln(); $pdf->SetTextColor(30, 41, 59); }
    private function row(\FPDF $pdf, array $values, array $widths): void { if ($pdf->GetY() > 190) $pdf->AddPage(); $pdf->SetFont('Arial', '', 7); foreach ($values as $i => $value) $pdf->Cell($widths[$i], 7, $this->text(mb_strimwidth((string) $value, 0, 38, '...')), 1); $pdf->Ln(); }

    private function transactionBlock(\FPDF $pdf, array $values, array $widths, string $detail): void
    {
        $tableWidth = array_sum($widths);
        $pdf->SetFont('Arial', '', 7.5);
        $detailText = $this->text('Detail : '.$detail);
        $detailLines = $this->wrap($pdf, $detailText, $tableWidth - 6);
        $detailHeight = max(8, count($detailLines) * 4 + 3);
        if ($pdf->GetY() + 8 + $detailHeight > 193) {
            $pdf->AddPage();
        }

        $pdf->SetFont('Arial', '', 7.5);
        foreach ($values as $index => $value) {
            $alignment = $index >= count($values) - 2 ? 'R' : 'L';
            $pdf->Cell($widths[$index], 8, $this->text(mb_strimwidth((string) $value, 0, 42, '...')), 1, 0, $alignment);
        }
        $pdf->Ln();
        $x = $pdf->GetX(); $y = $pdf->GetY();
        $pdf->SetFillColor(244, 248, 252); $pdf->Rect($x, $y, $tableWidth, $detailHeight, 'DF');
        $pdf->SetXY($x + 3, $y + 1.5); $pdf->SetFont('Arial', '', 7.2);
        $pdf->MultiCell($tableWidth - 6, 4, implode("\n", $detailLines), 0, 'L');
        $pdf->SetXY($x, $y + $detailHeight);
    }

    private function wrap(\FPDF $pdf, string $text, float $width): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = []; $line = '';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if ($line !== '' && $pdf->GetStringWidth($candidate) > $width) {
                $lines[] = $line; $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines ?: ['-'];
    }

    private function transactionDetail($transaction, $maintenance = null, $sale = null): string
    {
        if ($transaction->type_transaction === 'vente') {
            $asset = collect([
                $sale?->lot?->name ? 'Lot '.$sale->lot->name : null,
                ! $sale?->lot && $sale?->propriete?->lot?->name ? 'Lot '.$sale->propriete->lot->name : null,
                $sale?->propriete?->reference ? 'Propriete '.$sale->propriete->reference : null,
                $sale?->porte?->numero_porte ? 'Porte '.$sale->porte->numero_porte : null,
            ])->filter()->unique()->implode(' - ');
            return 'Acheteur : '.($sale?->acheteur?->name ?? 'Non renseigne')
                .' | Proprietaire : '.($sale?->proprietaire?->name ?? 'Non renseigne')
                .' | '.($asset ?: 'Bien non renseigne');
        }
        if ($transaction->type_transaction === 'maintenance') {
            $providers = $maintenance?->details?->pluck('maintenancier.name')->filter()->unique()->implode(', ') ?: 'Non renseigne';
            return ($maintenance?->titre ?: 'Maintenance')
                .' | Proprietaire : '.($maintenance?->proprietaire?->name ?? 'Non renseigne')
                .' | Lot : '.($maintenance?->lot?->name ?? 'Non renseigne')
                .' | Porte : '.($maintenance?->porte?->numero_porte ?? 'Non renseignee')
                .' | Prestataire : '.$providers;
        }
        if ($transaction->type_transaction === 'loyer') {
            return 'Locataire : '.($transaction->locataire?->name ?? 'Non renseigne').' | Periode : '.((string) $transaction->mois_payer ?: 'Non renseignee');
        }
        return (string) ($transaction->mois_payer ?: 'Operation de caisse');
    }
    private function money(mixed $value): string { return number_format((float) $value, 0, ',', ' '); }
    private function text(mixed $value): string { return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $value) ?: (string) $value; }
}
