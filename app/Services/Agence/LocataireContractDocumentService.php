<?php

namespace App\Services\Agence;

use App\Models\Agence;
use App\Models\Locataire;
use App\Models\LocataireAgence;
use Illuminate\Support\Str;

class LocataireContractDocumentService
{
    /** @var array<int, string> */
    private array $temporaryFiles = [];

    public function __construct(private readonly AgencyDocumentApproval $approval)
    {
    }

    public const TYPES = [
        'avis-locataire',
        'contrat-bail',
        'delegation-gerance',
        'etat-lieux',
        'fiche-renseignements',
        'procuration',
    ];

    public function generate(string $type, Agence $agence, Locataire $locataire, LocataireAgence $contrat, float $impayes = 0): string
    {
        $pdf = new class extends \FPDF {
            public string $footerText = '';

            public function Footer(): void
            {
                $this->SetY(-14);
                $this->SetDrawColor(0, 85, 155);
                $this->Line(15, $this->GetY(), $this->GetPageWidth() - 15, $this->GetY());
                $this->SetY(-11);
                $this->SetTextColor(95, 113, 130);
                $this->SetFont('Times', '', 7);
                $this->Cell(0, 4, $this->footerText . ' - Page ' . $this->PageNo(), 0, 0, 'C');
                $this->SetTextColor(0, 0, 0);
            }
        };

        $pdf->SetMargins(16, 15, 16);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->footerText = $this->encode($this->agencyFooter($agence));

        try {
            match ($type) {
                'avis-locataire' => $this->renderAvis($pdf, $agence, $locataire, $contrat, $impayes),
                'contrat-bail' => $this->renderBail($pdf, $agence, $locataire, $contrat),
                'delegation-gerance' => $this->renderDelegation($pdf, $agence, $contrat),
                'etat-lieux' => $this->renderEtatLieux($pdf, $agence, $locataire, $contrat),
                'fiche-renseignements' => $this->renderFiche($pdf, $agence, $locataire, $contrat),
                'procuration' => $this->renderProcuration($pdf, $agence, $locataire, $contrat),
                default => throw new \InvalidArgumentException('Type de document locataire invalide.'),
            };

            $this->approval->applyToFpdf($pdf, $agence);

            return $pdf->Output('S');
        } finally {
            foreach ($this->temporaryFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $this->temporaryFiles = [];
        }
    }

    private function renderAvis(\FPDF $pdf, Agence $agence, Locataire $tenant, LocataireAgence $contract, float $impayes): void
    {
        $this->addPage($pdf, $agence);
        $this->line($pdf, 'À l’attention du locataire', true, 13);
        $this->space($pdf, 3);
        $this->line($pdf, 'Objet : Information', true);
        $this->space($pdf, 3);
        $this->paragraph($pdf, 'Nous venons par la présente vous informer que, suite au transfert de gérance intervenu entre '
            . $this->value($contract->proprietaire?->name) . ' et l’agence ' . mb_strtoupper($agence->name)
            . ', vous êtes prié(e), dans un souci de concordance des informations, de vérifier et compléter la présente fiche de renseignements.');
        $this->infoLine($pdf, 'Nom et prénoms', $tenant->name);
        $this->infoLine($pdf, 'Profession', $tenant->profession);
        $this->infoLine($pdf, 'Téléphone', $this->phones($tenant->tel1, $tenant->tel2));
        $this->infoLine($pdf, 'Date d’entrée', $this->date($contract->date_entree));
        $this->infoLine($pdf, 'Porte occupée', $contract->porte?->numero_porte);
        $this->infoLine($pdf, 'Nombre de personnes', $contract->nbre_personne);
        $this->infoLine($pdf, 'Nombre d’enfants', $contract->nbre_enfant);
        $this->infoLine($pdf, 'Montant du loyer', $this->money($this->rent($contract)));
        $this->infoLine($pdf, 'Caution payée', $this->money($this->deposit($contract)));
        $this->infoLine($pdf, 'Loyers impayés au ' . now()->format('d/m/Y'), $this->money($impayes));
        $this->space($pdf, 4);
        $this->heading($pdf, 'PERSONNE À CONTACTER EN CAS D’URGENCE');
        $this->infoLine($pdf, 'Nom et prénoms', $contract->name_representant);
        $this->infoLine($pdf, 'Téléphone', $contract->contant_representant);
        $this->infoLine($pdf, 'Adresse', $contract->adresse_representant);
        $this->paragraph($pdf, 'Tout en vous remerciant pour votre bonne collaboration, veuillez recevoir nos fraternelles salutations.');
        $this->paragraph($pdf, 'NB : Veuillez retourner cette fiche après vérification, accompagnée de la photocopie de votre pièce d’identité, du reçu de la caution payée et du reçu du dernier paiement.');
        $this->addPage($pdf, $agence);
        $this->line($pdf, 'Fait à ' . $this->value($agence->adresse) . ', le ' . now()->format('d/m/Y'), false, 11, 'R');
        $this->space($pdf, 15);
        $this->line($pdf, 'Le locataire', true, 11, 'R');
        $this->line($pdf, $tenant->name, false, 11, 'R');
    }

    private function renderBail(\FPDF $pdf, Agence $agence, Locataire $tenant, LocataireAgence $contract): void
    {
        $this->addPage($pdf, $agence, 'CONTRAT DE BAIL À USAGE D’HABITATION');
        $this->heading($pdf, 'ENTRE LES SOUSSIGNÉS :');
        $representative = $agence->responsable?->name ?: $agence->parametrage?->dg_nom;
        $this->paragraph($pdf, 'L’agence « ' . mb_strtoupper($agence->name) . ' », ' . $this->value($agence->regime_fiscal, 'agence immobilière')
            . ', ayant son siège à ' . $this->value($agence->adresse) . ', ' . $this->value($agence->bp)
            . ', email : ' . $this->value($agence->email1) . ', représentée par ' . $this->value($representative)
            . ', joignable aux numéros : ' . $this->phones($agence->tel1, $agence->tel2) . '.');
        $owner = $contract->proprietaire;
        $this->paragraph($pdf, 'Agissant en qualité de mandataire au nom et pour le compte de ' . $this->value($owner?->name)
            . ', propriétaire immobilier, né(e) le ' . $this->date($owner?->date_naiss) . ' à ' . $this->value($owner?->lieu_naiss)
            . ', de nationalité ' . $this->value($owner?->nationalite) . ', titulaire de la pièce N° '
            . $this->value($owner?->numpiece) . ', domicilié(e) à ' . $this->value($owner?->adresse) . '.');
        $this->line($pdf, 'Ci-après désigné « LE BAILLEUR »', true);
        $this->space($pdf, 3);
        $this->paragraph($pdf, $tenant->name . ', né(e) le ' . $this->date($tenant->date_naissance) . ' à '
            . $this->value($tenant->lieu_naissance) . ', de nationalité ' . $this->value($tenant->nationalite)
            . ', titulaire de la pièce N° ' . $this->value($tenant->num_piece) . ', domicilié(e) à '
            . $this->value($tenant->adresse) . ', profession : ' . $this->value($tenant->profession)
            . ', occupant la porte N° ' . $this->value($contract->porte?->numero_porte) . ', contacts : '
            . $this->phones($tenant->tel1, $tenant->tel2) . '.');
        $this->line($pdf, 'Ci-après désigné « LE PRENEUR »', true);
        $this->paragraph($pdf, 'Il a été convenu et arrêté ce qui suit : le BAILLEUR met en location à usage d’habitation le local situé à '
            . $this->propertyAddress($contract) . '. Le bail est conclu pour une durée d’une (01) année renouvelable par tacite reconduction. Il court du '
            . $this->date($contract->date_debut_bail ?: $contract->date_entree) . ' au ' . $this->date($contract->date_fin_bail) . '.');

        $this->addPage($pdf, $agence, 'CONTRAT DE BAIL À USAGE D’HABITATION');
        $this->paragraph($pdf, 'Toute décision de résiliation devra être notifiée à l’autre partie trois (03) mois avant son terme.');
        $this->heading($pdf, 'A- CLAUSES ET CONDITIONS');
        $this->clause($pdf, '1- USAGE', 'Les locaux serviront exclusivement à usage d’habitation. Tout autre usage engage la responsabilité du PRENEUR et peut entraîner la résiliation du bail.');
        $this->clause($pdf, '2- ENTRETIEN ET RÉPARATIONS', 'Le PRENEUR entretiendra les lieux et assumera les réparations locatives. Le BAILLEUR ne prendra en charge que les grosses réparations nécessaires non imputables au PRENEUR. Le PRENEUR laissera accéder aux lieux les ouvriers mandatés pour les travaux utiles et prend les locaux dans l’état constaté lors de l’entrée.');
        $this->clause($pdf, '3- RÈGLEMENTS URBAINS', 'Les consommations et factures CIE et SODECI postérieures à l’entrée sont à la charge du PRENEUR. Celui-ci respectera la réglementation, le voisinage, la salubrité et la tranquillité des lieux. Les nuisances sonores, dépôts d’ordures dans les espaces communs et dégradations sont interdits.');
        $this->clause($pdf, '4- CESSION DE BAIL', 'Le bail est consenti intuitu personae. Toute cession, sous-location ou mise à disposition des lieux à un tiers sans accord écrit du BAILLEUR est interdite et peut entraîner la résiliation immédiate.');

        $this->addPage($pdf, $agence, 'CONTRAT DE BAIL À USAGE D’HABITATION');
        $this->heading($pdf, 'B- LE LOYER');
        $this->paragraph($pdf, 'Le présent bail est consenti moyennant un loyer mensuel de ' . $this->money($this->rent($contract))
            . ', payable au plus tard le 05 du mois en cours auprès de l’agence ou par les moyens de paiement qu’elle communique. Tout retard peut entraîner les pénalités prévues par la réglementation et le contrat.');
        $this->paragraph($pdf, 'Aucune échéance ne sera différée, sauf cas de force majeure dûment justifié et accepté. Le défaut de paiement expose le PRENEUR aux procédures de recouvrement et de résiliation prévues par les textes en vigueur.');
        $this->heading($pdf, 'C- ÉTAT DES LIEUX');
        $this->paragraph($pdf, 'Un procès-verbal contradictoire d’état des lieux est dressé avant la remise des clés et annexé au présent contrat.');
        $this->heading($pdf, 'D- DÉPÔT DE GARANTIE ET AVANCE');
        $this->paragraph($pdf, 'Le PRENEUR verse un dépôt de garantie de ' . $this->money($this->deposit($contract))
            . ' et une avance sur loyer de ' . $this->money($this->advanceAmount($contract)) . '. Le montant global enregistré à l’entrée est de '
            . $this->money((float) $contract->montant_global_garantie) . '.');
        $this->paragraph($pdf, 'Le dépôt de garantie n’est pas un loyer. Il est restitué à la fin du bail après remise des clés, apurement des comptes et déduction, le cas échéant, des réparations locatives et sommes restant dues.');

        $this->addPage($pdf, $agence, 'CONTRAT DE BAIL À USAGE D’HABITATION');
        $this->clause($pdf, 'E- TRAVAUX ET RÉPARATIONS', 'Les grosses réparations sont à la charge du BAILLEUR. Les transformations de convenance personnelle et la remise en état résultant des dégradations sont à la charge du PRENEUR, qui restituera le local dans son état initial sous réserve de l’usure normale.');
        $this->clause($pdf, 'F- ÉLECTION DE DOMICILE ET ATTRIBUTION DE JURIDICTION', 'Tout litige relatif à l’interprétation ou à l’exécution du bail fera d’abord l’objet d’une tentative de règlement amiable. À défaut, les juridictions territorialement compétentes pourront être saisies.');
        $this->paragraph($pdf, 'Le présent contrat sert de loi entre les parties. Il est établi en deux (02) originaux, un pour chaque partie, et prend effet à sa date de signature.');
        $this->line($pdf, 'Fait à ' . $this->value($agence->adresse) . ', le ' . now()->format('d/m/Y'), false, 11, 'R');
        $this->space($pdf, 8);
        $this->line($pdf, 'SIGNATURES', true, 12, 'C');
        $this->signatureColumns($pdf, $agence->name . "\n" . $this->value($representative), "LE PRENEUR\n" . $tenant->name);
    }

    private function renderDelegation(\FPDF $pdf, Agence $agence, LocataireAgence $contract): void
    {
        $this->addPage($pdf, $agence);
        $owner = $contract->proprietaire;
        $this->line($pdf, $this->value($owner?->name), true, 12);
        $this->line($pdf, 'Propriétaire immobilier');
        $this->line($pdf, 'Téléphone : ' . $this->phones($owner?->tel1, $owner?->tel2));
        $this->line($pdf, now()->format('d/m/Y'), false, 10, 'R');
        $this->space($pdf, 8);
        $this->line($pdf, 'À Mesdames, Mesdemoiselles et Messieurs les locataires de la propriété de ' . $this->value($owner?->name), false, 11, 'R');
        $this->space($pdf, 8);
        $this->line($pdf, 'OBJET : DÉLÉGATION DE GÉRANCE', true, 12);
        $this->space($pdf, 5);
        $this->paragraph($pdf, 'Mesdames, Mesdemoiselles, Messieurs, chers locataires, je vous informe que je n’assurerai plus directement la gestion de la propriété que vous occupez. Celle-ci est désormais confiée à l’agence '
            . mb_strtoupper($agence->name) . ', représentée par ' . $this->value($agence->responsable?->name ?: $agence->parametrage?->dg_nom) . '.');
        $this->paragraph($pdf, 'À compter de cette notification, veuillez vous adresser directement à l’agence aux numéros '
            . $this->phones($agence->tel1, $agence->tel2) . '. Son siège est situé à ' . $this->value($agence->adresse) . '.');
        $this->paragraph($pdf, 'Tout en vous remerciant pour les bons moments passés ensemble, veuillez agréer l’expression de mes salutations distinguées.');
        $this->space($pdf, 15);
        $this->line($pdf, $this->value($owner?->name), true, 11, 'R');
    }

    private function renderEtatLieux(\FPDF $pdf, Agence $agence, Locataire $tenant, LocataireAgence $contract): void
    {
        [$roomsA, $roomsB] = $this->roomsForContract($contract);
        $doorType = $this->value($contract->porte?->typePorte?->libelle);
        $this->addPage($pdf, $agence, 'ÉTAT DES LIEUX D’ENTRÉE CONTRADICTOIRE');
        $this->stateIdentityBox($pdf, $tenant, $contract, $doorType);
        $this->stateLegend($pdf);
        $this->conditionTable($pdf, $roomsA, ['SOL', 'PEINTURE DES MURS', 'PEINTURE DES PLAFONDS', 'PORTES', 'FENÊTRES', 'ANTI-VOLS', 'PLACARDS'], 'PIÈCES PRINCIPALES');

        $this->addPage($pdf, $agence, 'ÉTAT DES LIEUX D’ENTRÉE CONTRADICTOIRE');
        $this->stateLegend($pdf);
        $this->conditionTable($pdf, $roomsA, ['ÉLECTRICITÉ', 'NOMBRE DE CLÉS'], 'ÉQUIPEMENTS DES PIÈCES PRINCIPALES');
        $this->space($pdf, 5);
        $this->stateSectionTitle($pdf, 'OBSERVATIONS GÉNÉRALES');
        $this->writingLines($pdf, 6);
        $this->conditionTable($pdf, $roomsB, ['SOL', 'PEINTURE DES MURS', 'PEINTURE DES PLAFONDS', 'PORTES', 'ÉLECTRICITÉ'], 'CUISINE, SANITAIRES ET ANNEXES');

        $this->addPage($pdf, $agence, 'ÉTAT DES LIEUX D’ENTRÉE CONTRADICTOIRE');
        $this->stateLegend($pdf);
        $this->conditionTable($pdf, $roomsB, ['ROBINETTERIE', 'ÉVIER / LAVABO', 'DOUCHE ET SDB', 'CHASSE D’EAU / CUVETTE', 'NOMBRE DE CLÉS'], 'ÉQUIPEMENTS DES SANITAIRES ET ANNEXES');
        $this->space($pdf, 5);
        $this->stateSectionTitle($pdf, 'OBSERVATIONS GÉNÉRALES');
        $this->writingLines($pdf, 6);
        $this->space($pdf, 8);
        $this->signatureColumns($pdf, 'LE BAILLEUR', 'LE PRENEUR' . "\n" . $tenant->name);
        $this->line($pdf, 'Fait le : ____ / ____ / ______', false, 10, 'C');
    }

    private function renderFiche(\FPDF $pdf, Agence $agence, Locataire $tenant, LocataireAgence $contract): void
    {
        $this->addPage($pdf, $agence, 'FICHE DE RENSEIGNEMENTS DU LOCATAIRE');
        $this->infoLine($pdf, 'Propriété / cité', $contract->propriete?->reference);
        $this->infoLine($pdf, 'Lot', $contract->lot?->num_lot ?: $contract->lot?->name);
        $this->infoLine($pdf, 'Îlot', $contract->lot?->num_ilot);
        $this->infoLine($pdf, 'Porte', $contract->porte?->numero_porte);
        $this->infoLine($pdf, 'Caution', $this->money($this->deposit($contract)));
        $this->infoLine($pdf, 'Date d’entrée', $this->date($contract->date_entree));
        $this->infoLine($pdf, 'Nombre de personnes', $contract->nbre_personne);
        $this->infoLine($pdf, 'Nombre d’enfants', $contract->nbre_enfant);
        $this->infoLine($pdf, 'Montant du loyer', $this->money($this->rent($contract)));
        $this->infoLine($pdf, 'Nom et prénoms', $tenant->name);
        $this->infoLine($pdf, 'Nationalité', $tenant->nationalite);
        $this->infoLine($pdf, 'Profession', $tenant->profession);
        $this->infoLine($pdf, 'Lieu de travail', null);
        $this->space($pdf, 5);
        $this->heading($pdf, 'PERSONNE À CONTACTER EN CAS D’URGENCE');
        $this->infoLine($pdf, 'Nom et prénoms', $contract->name_representant);
        $this->infoLine($pdf, 'Téléphone', $contract->contant_representant);
        $this->infoLine($pdf, 'Adresse', $contract->adresse_representant);
        $this->space($pdf, 8);
        $this->line($pdf, 'Fait à ' . $this->value($agence->adresse) . ', le ' . now()->format('d/m/Y'), false, 11, 'R');
        $this->space($pdf, 12);
        $this->signatureColumns($pdf, "SIGNATURE DU LOCATAIRE\n" . $tenant->name, 'AGENT COMMERCIAL');
    }

    private function renderProcuration(\FPDF $pdf, Agence $agence, Locataire $tenant, LocataireAgence $contract): void
    {
        $this->addPage($pdf, $agence, 'PROCURATION');
        $this->paragraph($pdf, 'Je soussigné(e) ' . $tenant->name . ', né(e) le ' . $this->date($tenant->date_naissance)
            . ' à ' . $this->value($tenant->lieu_naissance) . ', de nationalité ' . $this->value($tenant->nationalite)
            . ', titulaire de la pièce N° ' . $this->value($tenant->num_piece) . ', domicilié(e) à '
            . $this->value($tenant->adresse) . ', contacts : ' . $this->phones($tenant->tel1, $tenant->tel2)
            . ', occupant la porte N° ' . $this->value($contract->porte?->numero_porte) . ', profession : '
            . $this->value($tenant->profession) . ', locataire chez ' . $this->value($contract->proprietaire?->name) . '.');
        $this->space($pdf, 6);
        $this->paragraph($pdf, 'Donne procuration à l’agence immobilière ' . mb_strtoupper($agence->name)
            . ' pour accomplir, dans le respect de la législation et des procédures applicables, les démarches nécessaires à la conservation et à la sécurisation de mes effets en cas d’abandon avéré du local et d’impayés persistants.');
        $this->paragraph($pdf, 'La présente procuration est délivrée pour servir et valoir ce que de droit, sans renonciation aux droits et recours prévus par la loi.');
        $this->space($pdf, 8);
        $this->line($pdf, 'Fait à ' . $this->value($agence->adresse) . ', le ' . now()->format('d/m/Y'), false, 11, 'R');
        $this->space($pdf, 18);
        $this->line($pdf, 'Le locataire', true, 11, 'R');
        $this->line($pdf, $tenant->name, false, 11, 'R');
    }

    private function addPage(\FPDF $pdf, Agence $agence, ?string $title = null): void
    {
        $pdf->AddPage();
        $this->watermark($pdf, $agence);
        $this->logo($pdf, $agence);
        $pdf->SetY($title ? 18 : 40);
        if ($title) {
            $pdf->SetX(52);
            $pdf->SetFont('Times', 'B', 15);
            $pdf->SetDrawColor(0, 85, 155);
            $pdf->SetFillColor(0, 85, 155);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(143, 12, $this->encode($title), 1, 1, 'C', true);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->SetY(43);
        } else {
            $pdf->SetXY(52, 16);
            $pdf->SetTextColor(0, 85, 155);
            $pdf->SetFont('Times', 'B', 12);
            $pdf->Cell(143, 7, $this->encode(mb_strtoupper($agence->name)), 0, 1, 'R');
            $pdf->SetX(52);
            $pdf->SetTextColor(95, 113, 130);
            $pdf->SetFont('Times', '', 8);
            $pdf->Cell(143, 5, $this->encode($this->value($agence->adresse)), 0, 1, 'R');
            $pdf->SetTextColor(15, 23, 42);
            $pdf->SetY(40);
        }
    }

    private function clause(\FPDF $pdf, string $title, string $body): void
    {
        $this->heading($pdf, $title);
        $this->paragraph($pdf, $body);
    }

    private function conditionTable(\FPDF $pdf, array $rooms, array $items, ?string $sectionTitle = null): void
    {
        if ($sectionTitle) {
            $this->stateSectionTitle($pdf, $sectionTitle);
        }

        $available = $pdf->GetPageWidth() - 32;
        $designationWidth = 37;
        $roomWidth = ($available - $designationWidth) / count($rooms);
        $pdf->SetDrawColor(151, 177, 199);
        $pdf->SetFillColor(0, 85, 155);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Times', 'B', 7);
        $pdf->Cell($designationWidth, 12, $this->encode('DÉSIGNATION'), 1, 0, 'C', true);
        foreach ($rooms as $room) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell($roomWidth, 4, $this->encode($room), 1, 'C', true);
            $pdf->SetXY($x + $roomWidth, $y);
        }
        $pdf->Ln(12);
        $pdf->SetTextColor(15, 23, 42);
        foreach ($items as $index => $item) {
            $filled = $index % 2 === 0;
            $pdf->SetFillColor($filled ? 240 : 255, $filled ? 247 : 255, $filled ? 252 : 255);
            $pdf->SetFont('Times', 'B', 7);
            $pdf->Cell($designationWidth, 12, $this->encode($item), 1, 0, 'L', $filled);
            $pdf->SetFont('Times', '', 6);
            foreach ($rooms as $_room) {
                $pdf->Cell($roomWidth, 12, $this->encode('[ ] B   [ ] M   [ ] N/A'), 1, 0, 'C', $filled);
            }
            $pdf->Ln();
        }
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
    }

    private function stateIdentityBox(\FPDF $pdf, Locataire $tenant, LocataireAgence $contract, string $doorType): void
    {
        $pdf->SetFillColor(234, 244, 251);
        $pdf->SetDrawColor(151, 177, 199);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(43, 7, $this->encode('LOCATAIRE'), 1, 0, 'L', true);
        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(135, 7, $this->encode($tenant->name), 1, 1, 'L', true);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(43, 7, $this->encode('TYPE DE LOGEMENT'), 1, 0, 'L', true);
        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(55, 7, $this->encode($doorType), 1, 0, 'L', true);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(30, 7, $this->encode('PORTE N°'), 1, 0, 'L', true);
        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(50, 7, $this->encode($this->value($contract->porte?->numero_porte)), 1, 1, 'L', true);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(43, 7, $this->encode('ADRESSE'), 1, 0, 'L', true);
        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(135, 7, $this->encode($this->propertyAddress($contract)), 1, 1, 'L', true);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Ln(3);
    }

    private function stateLegend(\FPDF $pdf): void
    {
        $pdf->SetFillColor(239, 248, 226);
        $pdf->SetTextColor(54, 92, 12);
        $pdf->SetFont('Times', 'B', 8);
        $pdf->Cell(0, 7, $this->encode('LÉGENDE : B = BON     M = MAUVAIS     N/A = NON APPLICABLE'), 0, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);
    }

    private function stateSectionTitle(\FPDF $pdf, string $title): void
    {
        $pdf->SetFillColor(118, 194, 6);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(0, 7, $this->encode($title), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(2);
    }

    private function writingLines(\FPDF $pdf, int $count): void
    {
        for ($index = 0; $index < $count; $index++) {
            $pdf->Cell(0, 7, '', 'B', 1);
        }
    }

    private function signatureColumns(\FPDF $pdf, string $left, string $right): void
    {
        $pdf->SetFont('Times', 'B', 10);
        $pdf->MultiCell(82, 6, $this->encode($left), 0, 'C');
        $leftY = $pdf->GetY();
        $pdf->SetXY(112, $leftY - 12);
        $pdf->MultiCell(82, 6, $this->encode($right), 0, 'C');
        $pdf->SetY(max($leftY, $pdf->GetY()) + 12);
    }

    private function infoLine(\FPDF $pdf, string $label, mixed $value): void
    {
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(53, 7, $this->encode($label . ' :'), 0, 0);
        $pdf->SetFont('Times', '', 10);
        $pdf->MultiCell(0, 7, $this->encode($this->value($value)));
    }

    private function paragraph(\FPDF $pdf, string $text): void
    {
        $pdf->SetFont('Times', '', 10.5);
        $pdf->MultiCell(0, 6, $this->encode($text), 0, 'J');
        $pdf->Ln(2);
    }

    private function heading(\FPDF $pdf, string $text): void
    {
        $pdf->SetFillColor(234, 244, 251);
        $pdf->SetTextColor(0, 85, 155);
        $pdf->SetFont('Times', 'B', 10.5);
        $pdf->MultiCell(0, 7, $this->encode($text), 0, 'L', true);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Ln(1);
    }

    private function line(\FPDF $pdf, string $text, bool $bold = false, float $size = 10.5, string $align = 'L'): void
    {
        $pdf->SetFont('Times', $bold ? 'B' : '', $size);
        $pdf->MultiCell(0, 7, $this->encode($text), 0, $align);
    }

    private function space(\FPDF $pdf, float $height): void
    {
        $pdf->Ln($height);
    }

    private function rent(LocataireAgence $contract): float
    {
        return (float) ($contract->loyer_net ?: $contract->porte?->mt_loyer ?: $contract->porte?->tarifActif?->montant ?: 0);
    }

    /**
     * Construit les espaces de contrôle selon le type de porte, qui représente
     * dans cette application la taille réelle du logement.
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function roomsForContract(LocataireAgence $contract): array
    {
        $type = Str::lower(Str::ascii((string) ($contract->porte?->typePorte?->libelle ?? '')));

        if (str_contains($type, 'magasin')) {
            return [
                ['ESPACE COMMERCIAL'],
                ['RÉSERVE', 'TOILETTES / WC'],
            ];
        }

        if (str_contains($type, 'studio')) {
            return [
                ['PIÈCE PRINCIPALE'],
                ['CUISINE / KITCHENETTE', 'SALLE D’EAU', 'TOILETTES / WC'],
            ];
        }

        if (str_contains($type, 'entree') && str_contains($type, 'coucher')) {
            return [
                ['ENTRÉE / COUCHER'],
                ['CUISINE', 'SALLE D’EAU', 'TOILETTES / WC'],
            ];
        }

        $pieceCount = match (true) {
            str_contains($type, 'deux') || preg_match('/\b2\b/', $type) === 1 => 2,
            str_contains($type, 'trois') || preg_match('/\b3\b/', $type) === 1 => 3,
            str_contains($type, 'quatre') || preg_match('/\b4\b/', $type) === 1 => 4,
            str_contains($type, 'cinq') || preg_match('/\b5\b/', $type) === 1 => 5,
            str_contains($type, 'six') || preg_match('/\b6\b/', $type) === 1 => 6,
            default => 1,
        };

        if ($pieceCount === 1) {
            return [
                ['PIÈCE PRINCIPALE'],
                ['CUISINE', 'SALLE D’EAU', 'TOILETTES / WC'],
            ];
        }

        $mainRooms = ['SALON / SÉJOUR'];
        for ($bedroom = 1; $bedroom < $pieceCount; $bedroom++) {
            $mainRooms[] = $bedroom === 1 ? 'CHAMBRE PRINCIPALE' : 'CHAMBRE ' . $bedroom;
        }

        return [
            $mainRooms,
            ['CUISINE', 'SALLE D’EAU', 'TOILETTES / WC', 'BALCON / TERRASSE'],
        ];
    }

    private function deposit(LocataireAgence $contract): float
    {
        $value = (float) ($contract->caution ?: $contract->porte?->caution ?: 0);

        return $value > 0 && $value <= 24 ? $value * $this->rent($contract) : $value;
    }

    private function advanceAmount(LocataireAgence $contract): float
    {
        $value = (float) ($contract->avance ?: $contract->porte?->avance ?: 0);

        return $value > 0 && $value <= 24 ? $value * $this->rent($contract) : $value;
    }

    private function propertyAddress(LocataireAgence $contract): string
    {
        return $this->value($contract->propriete?->adresse_complete ?: $contract->lot?->adresse ?: $contract->propriete?->reference);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
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

    private function agencyFooter(Agence $agence): string
    {
        return collect([$agence->name, $agence->rccm ? 'RCCM ' . $agence->rccm : null, $agence->adresse,
            $this->phones($agence->tel1, $agence->tel2), $agence->email1])->filter()->implode(' - ');
    }

    private function logo(\FPDF $pdf, Agence $agence): void
    {
        if ($path = $this->logoPath($agence)) {
            $pdf->Image($path, 16, 12, 30, 0);
        }
    }

    private function watermark(\FPDF $pdf, Agence $agence): void
    {
        $sourcePath = $this->logoPath($agence);
        if (!$sourcePath || !extension_loaded('gd') || !($path = $this->transparentLogo($sourcePath))) {
            return;
        }
        [$width, $height] = getimagesize($path) ?: [0, 0];
        if ($width <= 0 || $height <= 0) return;
        $displayWidth = 105.0;
        $displayHeight = min(125.0, $displayWidth * $height / $width);
        if ($displayHeight === 125.0) $displayWidth = $displayHeight * $width / $height;
        $pdf->Image($path, ($pdf->GetPageWidth() - $displayWidth) / 2, ($pdf->GetPageHeight() - $displayHeight) / 2, $displayWidth, $displayHeight, 'PNG');
    }

    private function logoPath(Agence $agence): ?string
    {
        return app(AgencyDocumentBranding::class)->localLogoPath($agence);
    }

    private function transparentLogo(string $sourcePath): ?string
    {
        $source = match (exif_imagetype($sourcePath)) {
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            default => false,
        };
        if (!$source) return null;
        $canvas = imagecreatetruecolor(imagesx($source), imagesy($source));
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
        imagecopy($canvas, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
        imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 112);
        $base = tempnam(sys_get_temp_dir(), 'prosimmobilier-watermark-');
        if ($base === false) return null;
        $path = $base . '.png';
        @unlink($base);
        if (!imagepng($canvas, $path)) return null;
        $this->temporaryFiles[] = $path;
        return $path;
    }

    private function encode(string $value): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $value) ?: $value;
    }
}
