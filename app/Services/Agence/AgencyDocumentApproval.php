<?php

namespace App\Services\Agence;

use App\Models\Agence;

class AgencyDocumentApproval
{
    public function data(Agence $agence, string $documentType = 'document', ?float $amount = null): array
    {
        $agence->loadMissing('parametrage');
        $settings = $agence->parametrage;

        if (! $settings) {
            return ['cachet' => null, 'signatures' => []];
        }

        $signatures = [];
        $requiresDirector = $documentType === 'vente'
            || ($documentType === 'facture' && (bool) $settings->sig_dg_facture);
        $requiresAccountant = in_array($documentType, ['recu', 'vente'], true);
        $requiresDoubleSignature = (bool) $settings->sig_double
            && $amount !== null
            && $amount >= (float) ($settings->seuil_dg ?? 1000000);

        if ($requiresDirector || $requiresDoubleSignature) {
            $signatures[] = $this->signature(
                $settings->signature_dg,
                $settings->dg_nom ?: $agence->responsable?->name,
                $settings->dg_titre ?: 'Directeur Général'
            );
        }

        if ($requiresAccountant) {
            $signatures[] = $this->signature(
                $settings->signature_cpt,
                $settings->cpt_nom,
                $settings->cpt_titre ?: 'Responsable Comptable'
            );
        }

        if ($requiresDoubleSignature && ! $requiresAccountant) {
            $signatures[] = $this->signature(
                $settings->signature_cpt,
                $settings->cpt_nom,
                $settings->cpt_titre ?: 'Responsable Comptable'
            );
        }

        return [
            'cachet' => (bool) $settings->cachet_auto ? $this->image($settings->cachet) : null,
            'signatures' => array_values(array_filter($signatures, fn (array $signature) => $signature['image'] || $signature['name'])),
        ];
    }

    public function applyToFpdf(\FPDF $pdf, Agence $agence, string $documentType = 'document', ?float $amount = null): void
    {
        $approval = $this->data($agence, $documentType, $amount);
        $signatures = $approval['signatures'];
        $cachet = $approval['cachet'];

        if (! $cachet && $signatures === []) {
            return;
        }

        $requiredHeight = $signatures === [] ? 32 : 55;
        if ($pdf->GetY() > 297 - 18 - $requiredHeight) {
            $pdf->AddPage();
        }

        $startY = max($pdf->GetY() + 5, 220);
        $columnWidth = 62;
        $startX = 16;

        foreach ($signatures as $index => $signature) {
            $x = $startX + ($index * $columnWidth);
            if ($signature['image']) {
                $pdf->Image($signature['image'], $x + 12, $startY, 38, 16, 'PNG');
            }
            $pdf->SetXY($x, $startY + 18);
            $pdf->SetFont('Times', 'B', 8);
            $pdf->Cell($columnWidth, 4, $this->encode($signature['name'] ?: $signature['title']), 0, 2, 'C');
            $pdf->SetFont('Times', '', 7);
            $pdf->Cell($columnWidth, 4, $this->encode($signature['title']), 0, 0, 'C');
        }

        if ($cachet) {
            $cachetX = $signatures === [] ? 166 : 150;
            $pdf->Image($cachet, $cachetX, $startY, 28, 0, 'PNG');
        }
    }

    private function signature(mixed $image, mixed $name, mixed $title): array
    {
        return [
            'image' => $this->image($image),
            'name' => trim((string) $name),
            'title' => trim((string) $title),
        ];
    }

    private function image(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $path = ltrim(urldecode($value), '/');
        $relative = preg_replace('#^(?:public/|storage/)#', '', $path);

        foreach (array_unique([
            public_path($path),
            storage_path('app/public/'.$relative),
            public_path('storage/'.$relative),
        ]) as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function encode(string $value): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $value) ?: $value;
    }
}
