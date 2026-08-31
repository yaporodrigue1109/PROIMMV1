<?php

namespace App\Services\Agence;

use App\Models\LocataireAgence;
use App\Models\TransactionAgence;

class LoyerReceiptService
{
    public function __construct(private AgencyDocumentBranding $branding, private AgencyDocumentApproval $approval) {}

    public function generate(TransactionAgence $t): string
    {
        $t->loadMissing(['agence.parametrage', 'agence.ville', 'locataire.genre', 'porte.batiment', 'propriete', 'modePaiement']);
        $a = $t->agence;
        $bail = LocataireAgence::withoutGlobalScopes()->with(['proprietaire', 'lot'])
            ->where('agence_id', $t->agence_id)->where('locataire_id', $t->locataire_id)
            ->where('porte_id', $t->porte_id)->latest('created_at')->first();
        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(12, 10, 12); $pdf->SetAutoPageBreak(false); $pdf->AddPage();
        $blue = [16, 55, 112]; $red = [225, 45, 45];
        $pdf->SetDrawColor(190, 205, 225); $pdf->Rect(9, 9, 279, 192);

        // Grand logo discret au centre, comme sur le modèle de quittance fourni.
        if ($watermark = $this->branding->watermarkPath($a)) {
            try { $pdf->Image($watermark, 91, 45, 115, 115); } catch (\Throwable) {}
        }

        if ($logo = $this->branding->localLogoPath($a)) {
            try { $pdf->Image($logo, 16, 15, 22, 22); } catch (\Throwable) {}
        }
        // Le logo et les coordonnees occupent des colonnes distinctes afin
        // d'eviter tout chevauchement, quelle que soit la forme du logo.
        $pdf->SetTextColor(...$blue); $pdf->SetXY(44, 15); $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(125, 6, $this->txt($a?->name ?: 'Agence immobiliere'), 0, 1);
        $pdf->SetFont('Arial', '', 8.5);
        foreach (array_filter([$a?->adresse, $a?->code_agence ? 'Code agence : '.$a->code_agence : null,
            implode(' / ', array_filter([$a?->tel1, $a?->tel2])), implode(' / ', array_filter([$a?->email1, $a?->email2]))]) as $line) {
            $pdf->SetX(44); $pdf->Cell(125, 4.5, $this->txt($line), 0, 1);
        }

        $property = $bail?->lot?->name
            ?: 'Non renseigne';
        $rent = (float) ($bail?->loyer_net ?: $t->porte?->mt_loyer ?: 0);
        $y = 18;
        foreach ([
            ['Nom du bailleur', $bail?->proprietaire?->name ?: 'Non renseigne', ''], ['COUR N°', $property, ''],
            ['Porte N°', $t->porte?->numero_porte ?: 'Non renseignee', ''],
            [($a?->ville?->name ?: 'Abidjan').', le', optional($t->date_transaction)->format('d-m-Y') ?: now()->format('d-m-Y'), ''],
            ['Loyer net', number_format($rent, 0, ',', ' '), 'FCFA'],
            ['Montant paye', number_format((float) $t->montant_global_verser, 0, ',', ' '), 'FCFA'],
        ] as [$label, $value, $currency]) {
            $pdf->SetXY(181, $y); $pdf->SetFont('Arial', '', 9); $pdf->Cell(25, 6, $this->txt($label));
            $pdf->SetFont('Arial', $label === 'Nom du bailleur' ? 'B' : '', 9); $pdf->Cell(66, 6, $this->txt($value), 'B');
            $pdf->Cell(13, 6, $currency, 0, 0, 'R'); $y += 7;
        }

        $pdf->SetXY(100, 69); $pdf->SetFont('Times', 'B', 18);
        $pdf->Cell(58, 10, $this->txt('Quittance de loyer'), 0, 0, 'R');
        $pdf->SetTextColor(...$red); $pdf->Cell(65, 10, $this->txt('N° '.$t->numero_recu)); $pdf->SetTextColor(...$blue);
        $tenantName = trim(implode(' ', array_filter([
            $this->tenantTitle($t->locataire?->genre?->name),
            $t->locataire?->name,
        ])));
        $this->field($pdf, 16, 88, 'Recu de', $tenantName ?: 'Non renseigne', 280);
        $this->field($pdf, 16, 99, 'La somme de', $this->words((float) $t->montant_global_verser), 280);
        $this->field($pdf, 16, 112, 'Pour le reglement du mois de', $this->periods($t->mois_payer), 280);

        $x = 16;
        foreach ([['Caution', (bool) $t->is_first], ['Avance', (float) $t->montant_avance_payer > 0],
            ['Loyer', (float) $t->montant_loyer_payer > 0], ['Arrieres', (float) $t->montant_arriere_payer > 0], ['Penalite', false]] as [$label, $checked]) {
            $x = $this->box($pdf, $x, 125, $label, $checked);
        }
        $this->field($pdf, 17, 139, 'Par cheque N°', '', 121); $this->field($pdf, 151, 139, 'Du', '', 210); $this->field($pdf, 218, 139, 'sur', '', 280);

        $pdf->SetXY(17, 151); $pdf->SetFont('Arial', '', 10); $pdf->Cell(25, 7, 'Mobile Money');
        $mode = mb_strtolower((string) $t->modePaiement?->name); $x = 43;
        foreach ([['Espece', str_contains($mode, 'esp')], ['MOOV Money', str_contains($mode, 'moov')],
            ['MTN Money', str_contains($mode, 'mtn')], ['ORANGE Money', str_contains($mode, 'orange')], ['WAVE', str_contains($mode, 'wave')]] as [$label, $checked]) {
            $x = $this->box($pdf, $x, 151, $label, $checked);
        }
        $arrears = (float) $t->montant_arriere_actuel;
        $this->field($pdf, 16, 166, 'Montant du loyer impaye', $arrears > 0 ? number_format($arrears, 0, ',', ' ').' FCFA' : 'Aucun impaye', 280);

        $pdf->SetXY(16, 178); $pdf->SetTextColor(...$red); $pdf->SetFont('Arial', '', 8.5);
        $delay = (int) ($a?->parametrage?->delai_paiement ?? 10); $penalty = (float) ($a?->parametrage?->penalite_retard ?? 0);
        $pdf->Cell(190, 5, $this->txt('NB : la periode de validite d un loyer est du 01 au dernier jour du mois.'), 0, 1);
        $pdf->SetX(16); $pdf->Cell(190, 5, $this->txt("Les penalites sont applicables apres le {$delay} avec un taux de {$penalty}% ajoute au loyer."));
        $pdf->SetTextColor(...$blue); $pdf->SetXY(231, 177); $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(48, 5, $this->txt('Signature et cachet du Gerant'), 0, 1, 'C');
        if ($a) {
            $approval = $this->approval->data($a, 'recu', (float) $t->montant_global_verser);
            if ($approval['cachet']) { try { $pdf->Image($approval['cachet'], 244, 184, 27); } catch (\Throwable) {} }
            $accountant = $approval['signatures'][0] ?? null;
            if (! empty($accountant['image'])) {
                try { $pdf->Image($accountant['image'], 225, 184, 38, 13); } catch (\Throwable) {}
            }
        }
        return $pdf->Output('S');
    }

    private function field(\FPDF $pdf, float $x, float $y, string $label, string $value, float $end): void
    {
        $pdf->SetXY($x, $y); $pdf->SetFont('Arial', '', 10); $w = $pdf->GetStringWidth($this->txt($label))+5;
        $pdf->Cell($w, 7, $this->txt($label)); $pdf->Cell($end-$x-$w, 7, $this->txt($value), 'B');
    }

    private function box(\FPDF $pdf, float $x, float $y, string $label, bool $checked): float
    {
        $pdf->SetXY($x, $y); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(5, 5, $checked ? 'X' : '', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 10); $w = $pdf->GetStringWidth($this->txt($label))+9; $pdf->Cell($w, 5, $this->txt($label));
        return $x+$w+7;
    }

    private function periods(mixed $value): string
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) ? implode(', ', $decoded) : ((string) $value ?: 'Non renseignee');
    }

    private function words(float $amount): string
    {
        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::SPELLOUT); $words = $formatter->format((int) round($amount));
            if ($words !== false) return ucfirst($words);
        }
        return number_format($amount, 0, ',', ' ').' francs CFA';
    }

    private function tenantTitle(mixed $gender): string
    {
        $value = mb_strtolower(trim((string) $gender));

        if (str_contains($value, 'fémin') || str_contains($value, 'femin') || str_contains($value, 'femme')) {
            return 'Mme';
        }

        if (str_contains($value, 'mascul') || str_contains($value, 'homme')) {
            return 'M.';
        }

        return trim((string) $gender);
    }

    private function txt(mixed $value): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $value) ?: (string) $value;
    }
}
