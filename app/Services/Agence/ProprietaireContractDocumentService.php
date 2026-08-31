<?php

namespace App\Services\Agence;

use App\Models\Agence;
use App\Models\Proprietaire;
use Illuminate\Support\Collection;

class ProprietaireContractDocumentService
{
    /** @var array<int, string> */
    private array $temporaryFiles = [];

    public function __construct(private readonly AgencyDocumentApproval $approval)
    {
    }

    public function generate(
        string $type,
        Agence $agence,
        Proprietaire $proprietaire,
        Collection $lots,
        Collection $proprietes
    ): string {
        $pdf = new class extends \FPDF {
            public string $documentTitle = '';
            public string $footerText = '';

            public function Footer(): void
            {
                $this->SetY(-14);
                $this->SetDrawColor(0, 85, 155);
                $this->Line(15, $this->GetY(), 195, $this->GetY());
                $this->SetY(-11);
                $this->SetTextColor(95, 113, 130);
                $this->SetFont('Times', '', 7);
                $this->Cell(0, 4, $this->footerText . ' - Page ' . $this->PageNo(), 0, 0, 'C');
                $this->SetTextColor(0, 0, 0);
            }
        };

        $pdf->SetMargins(16, 15, 16);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->SetTitle($this->encode($type === 'mandat' ? 'Mandat de gestion immobilière' : 'Procuration'));
        $pdf->footerText = $this->encode($this->agencyFooter($agence));

        try {
            if ($type === 'mandat') {
                $this->renderMandat($pdf, $agence, $proprietaire, $lots, $proprietes);
            } else {
                $this->renderProcuration($pdf, $agence, $proprietaire);
            }

            $this->approval->applyToFpdf($pdf, $agence);

            return $pdf->Output('S');
        } finally {
            foreach ($this->temporaryFiles as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    @unlink($temporaryFile);
                }
            }
            $this->temporaryFiles = [];
        }
    }

    private function renderMandat(\FPDF $pdf, Agence $agence, Proprietaire $owner, Collection $lots, Collection $properties): void
    {
        $this->addDocumentPage($pdf, $agence, 'MANDAT DE GESTION IMMOBILIERE');
        $this->line($pdf, $owner->name, true, 13);
        $this->space($pdf, 3);
        $this->line($pdf, 'Né(e) le : ' . $this->date($owner->date_naiss) . ' à ' . $this->value($owner->lieu_naiss)
            . ' de nationalité ' . $this->value($owner->nationalite));
        $this->line($pdf, 'Domicilié(e) à : ' . $this->value($owner->adresse));
        $this->line($pdf, 'Téléphone : ' . $this->phones($owner->tel1, $owner->tel2));
        $this->line($pdf, 'Profession : ' . $this->value($owner->profession));
        $this->line($pdf, ($owner->typePiece?->name ?: 'Pièce d’identité') . ' : N° ' . $this->value($owner->numpiece));
        $this->line($pdf, 'N° Compte Contribuable : ' . $this->value($owner->num_contribuable ?? null));
        $this->paragraph($pdf, 'Dénommé(e) ci-après « LE PROPRIÉTAIRE ou LE MANDANT ».');
        $this->underlined($pdf, 'D’une part');
        $this->line($pdf, 'ET', true);

        $agencyName = mb_strtoupper($agence->name);
        $legalForm = $this->value($agence->regime_fiscal, 'agence immobilière');
        $this->paragraph($pdf, "L’agence immobilière dénommée « {$agencyName} », {$legalForm}, dont le siège est fixé à "
            . $this->value($agence->adresse) . ', immatriculée au Registre du Commerce et du Crédit Mobilier sous le numéro '
            . $this->value($agence->rccm) . ', ' . $this->value($agence->bp) . ', email : '
            . $this->value($agence->email1) . ', joignable aux numéros : ' . $this->phones($agence->tel1, $agence->tel2) . '.');
        $representative = $agence->responsable?->name ?: $agence->parametrage?->dg_nom;
        $title = $agence->parametrage?->dg_titre ?: 'Responsable de l’agence';
        $this->paragraph($pdf, 'Représentée par ' . $this->value($representative) . ', ' . $title
            . ', dénommée ci-après « L’AGENCE ou LE MANDATAIRE ».');
        $this->underlined($pdf, 'D’autre part');
        $this->paragraph($pdf, 'LESQUELS ont convenu et arrêté ce qui suit :');

        $this->addDocumentPage($pdf, $agence, 'MANDAT DE GESTION IMMOBILIERE');
        $this->paragraph($pdf, 'Le PROPRIÉTAIRE susnommé confère à l’AGENCE qui accepte, le mandat exclusif de louer, gérer et administrer les biens immobiliers désignés ci-après :');
        $this->heading($pdf, '1- DÉSIGNATION');
        $this->paragraph($pdf, $this->propertyDesignation($lots, $properties));
        $this->paragraph($pdf, 'Le PROPRIÉTAIRE affirme avoir la propriété desdits biens en vertu d’un titre régulier.');
        $this->heading($pdf, '2- DURÉE');
        $this->paragraph($pdf, 'Le présent mandat est consenti pour une durée d’une (01) année à compter de ce jour, renouvelable par tacite reconduction. Il ne peut être rompu avant le terme de la première année. Par la suite, chacune des parties peut y mettre fin à tout moment, à charge pour la partie qui en prend l’initiative de donner à l’autre un préavis de trois (3) mois par lettre remise contre décharge ou par acte extrajudiciaire.');
        $this->heading($pdf, 'OBLIGATIONS DU PROPRIÉTAIRE');
        $this->heading($pdf, '3- RELATIONS AVEC LES LOCATAIRES');
        $this->paragraph($pdf, 'Le PROPRIÉTAIRE s’interdit de traiter directement avec le ou les locataires sauf en présence de l’AGENCE.');
        $this->heading($pdf, '4- ASSURANCE');
        $this->paragraph($pdf, 'Le PROPRIÉTAIRE déclare faire son affaire personnelle de la souscription d’une police d’assurance portant sur les biens susdésignés.');

        $this->addDocumentPage($pdf, $agence, 'MANDAT DE GESTION IMMOBILIERE');
        $this->heading($pdf, '5- ACCORD SUR LES TRAVAUX ET DEVIS');
        $this->paragraph($pdf, 'Le PROPRIÉTAIRE s’oblige, dans un délai maximum de trois (3) jours, à refuser ou donner son accord sur les devis proposés par l’AGENCE pour l’exécution de travaux à sa charge en vertu du bail. Sans réponse dans ce délai, les devis sont réputés acceptés. Le PROPRIÉTAIRE consent à ce que les réparations urgentes, notamment les ruptures de canalisation, courts-circuits électriques et autres urgences, soient exécutées rapidement lorsqu’il demeure injoignable.');
        $this->heading($pdf, '6- RÉMUNÉRATION');
        $commission = (float) ($agence->parametrage?->commission ?? 10);
        $this->paragraph($pdf, 'En contrepartie de l’administration des biens, le PROPRIÉTAIRE consent à payer à l’AGENCE une rémunération de '
            . rtrim(rtrim(number_format($commission, 2, ',', ' '), '0'), ',')
            . ' % du montant des loyers et charges encaissés, prélevée sur chaque relevé de compte de gestion. Cette rémunération ne fait pas obstacle aux honoraires de location et de rédaction de bail mis à la charge des locataires conformément aux usages et textes en vigueur.');
        $this->heading($pdf, 'MISSIONS ET OBLIGATIONS DE L’AGENCE');
        $this->heading($pdf, '7- LES BAUX');
        $this->paragraph($pdf, 'L’AGENCE pourra conclure, proroger et renouveler les baux, les résilier, donner ou accepter tous congés et faire dresser les états des lieux, en préservant les intérêts du PROPRIÉTAIRE. Elle veillera à réviser les loyers conformément au bail, aux dispositions légales et à la situation du marché immobilier.');

        $this->addDocumentPage($pdf, $agence, 'MANDAT DE GESTION IMMOBILIERE');
        $this->heading($pdf, '8- REDDITION DES COMPTES');
        $this->paragraph($pdf, 'L’AGENCE percevra les loyers et toutes sommes dues, donnera ou retirera quittances, titres, pièces et décharges. Elle rendra compte mensuellement de sa gestion au moyen d’un état détaillé des sommes perçues et dépensées, accompagné des justificatifs.');
        $this->paragraph($pdf, 'L’AGENCE est tenue par une obligation de moyens. Elle n’est pas responsable du non-paiement des loyers si elle a accompli les diligences nécessaires, notamment les relances et mises en demeure. Sa responsabilité pourra être engagée en cas de faute, négligence, erreur, omission ou retard dans l’exécution de sa mission.');
        $this->heading($pdf, '9- FRAIS D’ACTES EXTRAJUDICIAIRES ET DE JUSTICE');
        $this->paragraph($pdf, 'Les frais d’actes extrajudiciaires et de procédures judiciaires nécessités dans le cadre d’une location sont à la charge du PROPRIÉTAIRE. Leur engagement doit être autorisé par tout écrit émanant du PROPRIÉTAIRE.');
        $this->heading($pdf, '10- DÉTENTION DU DÉPÔT DE GARANTIE');
        $this->paragraph($pdf, 'L’AGENCE s’engage à percevoir et à reverser au PROPRIÉTAIRE le dépôt de garantie. En cas de rupture du mandat, le PROPRIÉTAIRE s’engage à restituer le dépôt de garantie conformément aux dispositions légales et aux conditions du contrat de bail.');
        $this->heading($pdf, '11- IMPÔTS FONCIERS');
        $this->paragraph($pdf, 'L’AGENCE s’engage à accomplir les retenues et reversements fiscaux applicables aux loyers perçus, conformément à la réglementation en vigueur.');

        $this->addDocumentPage($pdf, $agence, 'MANDAT DE GESTION IMMOBILIERE');
        $this->heading($pdf, '12- ATTRIBUTION DE JURIDICTION');
        $this->paragraph($pdf, 'En cas de litige, le PROPRIÉTAIRE et l’AGENCE conviennent de rechercher d’abord un règlement amiable. À défaut, les juridictions territorialement compétentes seront saisies.');
        $this->paragraph($pdf, 'En foi de quoi, le présent contrat est établi et signé en deux (2) exemplaires originaux pour servir et valoir ce que de droit.');
        $this->line($pdf, 'Fait à ' . $this->value($agence->adresse) . ', le ' . now()->format('d/m/Y'), false, 11, 'R');
        $this->space($pdf, 5);
        $this->line($pdf, 'Signatures précédées de la mention « Lu et approuvé »', true, 11, 'C');
        $this->space($pdf, 10);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(90, 6, $this->encode("L’AGENCE"), 0, 0, 'C');
        $pdf->Cell(90, 6, $this->encode('LE PROPRIÉTAIRE'), 0, 1, 'C');
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(90, 6, $this->encode($agence->name), 0, 0, 'C');
        $pdf->Cell(90, 6, $this->encode($owner->name), 0, 1, 'C');
        $pdf->Cell(90, 6, $this->encode($this->value($representative)), 0, 0, 'C');
    }

    private function renderProcuration(\FPDF $pdf, Agence $agence, Proprietaire $owner): void
    {
        $pdf->AddPage();
        $this->watermark($pdf, $agence);
        $this->logo($pdf, $agence);
        $pdf->SetXY(98, 18);
        $pdf->SetFont('Times', '', 15);
        $pdf->MultiCell(95, 8, $this->encode("RÉPUBLIQUE DE CÔTE D’IVOIRE\nUNION - DISCIPLINE - TRAVAIL"), 1, 'C');
        $pdf->Ln(18);
        $pdf->SetFont('Times', 'B', 18);
        $pdf->SetDrawColor(0, 85, 155);
        $pdf->SetFillColor(0, 85, 155);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 11, $this->encode('PROCURATION'), 1, 1, 'C', true);
        $pdf->SetTextColor(15, 23, 42);
        $this->space($pdf, 10);
        $this->line($pdf, 'Je soussigné(e) : ' . $owner->name, false, 12);
        $this->line($pdf, 'Né(e) le : ' . $this->date($owner->date_naiss) . ' à : ' . $this->value($owner->lieu_naiss), false, 12);
        $this->line($pdf, ($owner->typePiece?->name ?: 'Numéro de pièce d’identité') . ' : ' . $this->value($owner->numpiece), false, 12);
        $this->line($pdf, 'Nationalité : ' . $this->value($owner->nationalite), false, 12);
        $this->line($pdf, 'Téléphone : ' . $this->phones($owner->tel1, $owner->tel2), false, 12);
        $this->line($pdf, 'Domicilié(e) à : ' . $this->value($owner->adresse), false, 12);
        $this->line($pdf, 'Profession : ' . $this->value($owner->profession), false, 12);
        $this->space($pdf, 7);
        $this->paragraph($pdf, 'Donne procuration à l’agence immobilière ' . mb_strtoupper($agence->name) . '.');
        $this->space($pdf, 5);
        $this->paragraph($pdf, 'Motif : Pour me représenter dans toutes actions en justice et partout où besoin sera, en ce qui concerne la gestion de mes propriétés.');
        $this->space($pdf, 8);
        $this->line($pdf, 'Fait à ' . $this->value($agence->adresse) . ', le ' . now()->format('d/m/Y'), false, 11, 'R');
        $this->space($pdf, 18);
        $this->line($pdf, 'Signature du propriétaire', true, 11, 'R');
    }

    private function addDocumentPage(\FPDF $pdf, Agence $agence, string $title): void
    {
        $pdf->AddPage();
        $this->watermark($pdf, $agence);
        $this->logo($pdf, $agence);
        $pdf->SetXY(55, 18);
        $pdf->SetFont('Times', 'B', 16);
        $pdf->SetDrawColor(0, 85, 155);
        $pdf->SetFillColor(0, 85, 155);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(140, 12, $this->encode($title), 1, 1, 'C', true);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetY(43);
    }

    private function logo(\FPDF $pdf, Agence $agence): void
    {
        $path = $this->logoPath($agence);
        if (!$path) {
            return;
        }

        $pdf->Image($path, 16, 12, 32, 0);
    }

    private function watermark(\FPDF $pdf, Agence $agence): void
    {
        $logoPath = $this->logoPath($agence);
        if (!$logoPath || !extension_loaded('gd')) {
            return;
        }

        $watermarkPath = $this->createTransparentLogo($logoPath);
        if (!$watermarkPath) {
            return;
        }

        [$width, $height] = getimagesize($watermarkPath) ?: [0, 0];
        if ($width <= 0 || $height <= 0) {
            return;
        }

        $displayWidth = 105.0;
        $displayHeight = $displayWidth * ($height / $width);
        if ($displayHeight > 125) {
            $displayHeight = 125.0;
            $displayWidth = $displayHeight * ($width / $height);
        }

        $pdf->Image(
            $watermarkPath,
            (210 - $displayWidth) / 2,
            (297 - $displayHeight) / 2,
            $displayWidth,
            $displayHeight,
            'PNG'
        );
    }

    private function logoPath(Agence $agence): ?string
    {
        return app(AgencyDocumentBranding::class)->localLogoPath($agence);
    }

    private function createTransparentLogo(string $sourcePath): ?string
    {
        $type = exif_imagetype($sourcePath);
        $source = match ($type) {
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            default => false,
        };

        if (!$source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        // 88 % de transparence : le logo reste visible sans gêner la lecture.
        imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 112);
        $temporaryPath = tempnam(sys_get_temp_dir(), 'prosimmobilier-watermark-');
        if ($temporaryPath === false) {
            return null;
        }

        $pngPath = $temporaryPath . '.png';
        @unlink($temporaryPath);
        $saved = imagepng($canvas, $pngPath);

        if (!$saved) {
            @unlink($pngPath);
            return null;
        }

        $this->temporaryFiles[] = $pngPath;

        return $pngPath;
    }

    private function propertyDesignation(Collection $lots, Collection $properties): string
    {
        if ($lots->isEmpty() && $properties->isEmpty()) {
            return 'Les biens immobiliers confiés par le PROPRIÉTAIRE à l’AGENCE, suivant les justificatifs et états descriptifs annexés au présent mandat.';
        }

        $parts = $lots->map(function ($lot) {
            $identity = trim(collect([$lot->name, $lot->num_lot ? 'lot ' . $lot->num_lot : null, $lot->num_ilot ? 'îlot ' . $lot->num_ilot : null])->filter()->implode(', '));
            return $identity . ($lot->adresse ? ' sis à ' . $lot->adresse : '');
        })->filter();

        if ($parts->isEmpty()) {
            $parts = $properties->map(fn ($property) => trim(($property->reference ?: 'Bien immobilier') . ($property->adresse ? ' sis à ' . $property->adresse : '')));
        }

        return 'Les biens immobiliers suivants : ' . $parts->implode(' ; ') . '.';
    }

    private function agencyFooter(Agence $agence): string
    {
        return collect([
            $agence->name,
            $agence->rccm ? 'RCCM ' . $agence->rccm : null,
            $agence->num_contribuable ? 'N° CC ' . $agence->num_contribuable : null,
            $agence->adresse,
            $this->phones($agence->tel1, $agence->tel2),
            $agence->email1,
        ])->filter()->implode(' - ');
    }

    private function paragraph(\FPDF $pdf, string $text): void
    {
        $pdf->SetFont('Times', '', 11);
        $pdf->MultiCell(0, 6, $this->encode($text), 0, 'J');
        $pdf->Ln(2);
    }

    private function heading(\FPDF $pdf, string $text): void
    {
        $pdf->SetFillColor(234, 244, 251);
        $pdf->SetTextColor(0, 85, 155);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->MultiCell(0, 7, $this->encode($text), 0, 'L', true);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Ln(1);
    }

    private function line(\FPDF $pdf, string $text, bool $bold = false, int $size = 11, string $align = 'L'): void
    {
        $pdf->SetFont('Times', $bold ? 'B' : '', $size);
        $pdf->MultiCell(0, 7, $this->encode($text), 0, $align);
    }

    private function underlined(\FPDF $pdf, string $text): void
    {
        $pdf->SetFont('Times', 'U', 11);
        $pdf->Cell(0, 7, $this->encode($text), 0, 1);
    }

    private function space(\FPDF $pdf, int $height): void
    {
        $pdf->Ln($height);
    }

    private function phones(?string $first, ?string $second): string
    {
        return collect([$first, $second])->filter()->implode(' / ') ?: 'Non renseigné';
    }

    private function value(mixed $value, string $fallback = 'Non renseigné'): string
    {
        return filled($value) ? (string) $value : $fallback;
    }

    private function date(mixed $value): string
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : 'Non renseignée';
    }

    private function encode(string $value): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $value) ?: $value;
    }
}
