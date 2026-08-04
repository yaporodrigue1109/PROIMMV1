import React from 'react';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { PDFDownloadLink } from '@react-pdf/renderer';
import ReversementPdfDocument from './pdf';
import { Button } from '../../../components/ui/button';
import { Card } from '../../../components/ui/card';
import { cn } from '../../../lib/utils';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const fmtNombre = (value) => new Intl.NumberFormat('fr-FR').format(Math.round(Number(value ?? 0)));

const fmtDate = (iso) => {
    if (!iso) return '—';
    const date = new Date(iso);
    if (isNaN(date.getTime())) return '—';
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    return `${day}/${month}/${year}`;
};

// Même convertisseur nombre → lettres que sur la fiche active
const UNITES = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix',
    'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
const DIZAINES = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];

function centainesEnLettres(n) {
    let mots = '';
    const c = Math.floor(n / 100);
    const reste = n % 100;
    if (c > 0) {
        mots += (c > 1 ? UNITES[c] + ' cent' : 'cent') + (c > 1 && reste === 0 ? 's' : '');
        if (reste > 0) mots += ' ';
    }
    if (reste > 0) {
        if (reste < 20) {
            mots += UNITES[reste];
        } else {
            const d = Math.floor(reste / 10);
            const u = reste % 10;
            if (d === 7 || d === 9) {
                mots += DIZAINES[d - 1] + '-' + UNITES[10 + u];
            } else {
                mots += DIZAINES[d] + (u > 0 ? (u === 1 && d !== 8 ? ' et un' : '-' + UNITES[u]) : (d === 8 ? 's' : ''));
            }
        }
    }
    return mots;
}

function nombreEnLettres(n) {
    n = Math.round(Number(n) || 0);
    if (n === 0) return 'zéro';
    if (n < 0) return 'moins ' + nombreEnLettres(-n);
    const millions = Math.floor(n / 1000000);
    const milliers = Math.floor((n % 1000000) / 1000);
    const unites = n % 1000;
    let mots = [];
    if (millions > 0) mots.push((millions > 1 ? centainesEnLettres(millions) + ' millions' : 'un million'));
    if (milliers > 0) mots.push((milliers > 1 ? centainesEnLettres(milliers) + ' mille' : 'mille'));
    if (unites > 0) mots.push(centainesEnLettres(unites));
    return mots.join(' ').trim();
}

const montantEnLettres = (n) => {
    const mots = nombreEnLettres(n);
    return mots.charAt(0).toUpperCase() + mots.slice(1) + ' francs CFA';
};

   const courTotals = (c) => {
    let attendu = 0, totalPaye = 0, restant = 0, avance = 0, montantLoyer = 0 , montantCautionSodeci = 0, montantarrierePaye = 0,
     montantLoyerPaye = 0, montantNouvelleCaution = 0 , montantArrieresPaye = 0;

    c.locataires?.forEach(l => {
        attendu += l.montantAttendu || 0;
        totalPaye += l.totalPaye || 0;
        restant += l.restant || 0;
        avance += l.avance || 0;
        montantLoyer += l.montantLoyer || 0;
        montantCautionSodeci +=  l.cautionSodeci || 0; // Ajout de la caution SODECI au montant du loyer
        montantarrierePaye += l.arrierePaye || 0; // Ajout de l'arriéré payé au montant du loyer
        montantLoyerPaye += l.loyerPaye || 0; // Ajout du loyer payé au montant du loyer
        montantNouvelleCaution += l.nouvelleCaution || 0; // Ajout de la nouvelle caution au montant du loyer
        montantArrieresPaye += l.arrierePaye || 0; // Ajout de l'arriéré payé au montant du loyer
    });

    const commission = totalPaye   * (c.commissionRate || 0.10);
    const apresCommission = totalPaye - commission;
    const net = apresCommission + (c.nouvelleCaution || 0) - (c.depenses || 0);
    const pct = attendu > 0 ? Math.round((totalPaye / attendu) * 100) : 0;

    return { attendu, totalPaye, restant, avance, montantLoyer, montantCautionSodeci, montantarrierePaye, montantLoyerPaye, montantNouvelleCaution, montantArrieresPaye, commission, apresCommission, net, pct };
};



/**
 * Fiche archivée (lecture seule) — reprend les mêmes colonnes que la fiche
 * active, mais à partir des totaux déjà persistés dans `reversements` /
 * `reversement_details` au moment de la validation (pas de recalcul ici).
 *
 * ⚠️ `reversement_details` ne stocke pas encore : date_entree, caution_payee,
 * date_paiement, mois_payer, caution_sodeci, numero_recu. Tant que ces
 * colonnes ne sont pas ajoutées en base, ces cellules restent vides ('—')
 * dans l'archive, même si elles apparaissaient sur la fiche au moment du
 * reversement.
 */
export default function ReversementHistoriqueDetail({ cour }) {
    if (!cour) return null;

    const t = courTotals(cour) || {};
    const cell = 'border border-[#dbe3ea] px-2 py-1.5 text-[11px] leading-tight';
    const headCell = cn(cell, 'bg-[#f1f5f9] text-center font-semibold text-[#0f172a]');

    return (
        <AgenceLayout title="Fiche de reversement (archive)">
            <Head title="Fiche de reversement (archive)" />

            <div className="mx-auto flex w-full max-w-[1400px] flex-col gap-6">
                <Button
                    variant="outline"
                    className="w-fit gap-2 border-[#c8d4de] print:hidden"
                    onClick={() => router.visit('/agence/reversement/historique')}
                >
                    <ArrowLeft className="h-4 w-4" />
                    Retour à l'historique
                </Button>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm print:border-0 print:shadow-none">
                    <div className="flex flex-wrap gap-2 p-6 print:hidden">
                        <div className="rounded-xl bg-[#eef8df] px-4 py-3 text-sm font-medium text-[#4d8500]">
                            ✓ Ce reversement a déjà été effectué au bailleur.
                        </div>
                    <PDFDownloadLink
                        document={<ReversementPdfDocument cour={cour} />}
                        fileName={`reversement-${(cour.nom || 'fiche').replace(/\s+/g, '_')}.pdf`}
                        className={cn(agenceButtonStyles.outline, 'ml-auto inline-flex items-center gap-2 no-underline')}
                    >
                        {({ loading }) => (loading ? 'Préparation du PDF…' : '🖨️ Télécharger le PDF')}
                    </PDFDownloadLink>
                    </div>

                    <div className="p-6">
                        {/* En-tête du document */}
                        <div className="mb-4 flex items-start justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-[#00559b] text-sm font-bold text-white">
                                    {cour.logo_entreprise ? <img src={cour.logo_entreprise} alt="" /> : null}
                                </div>
                                <span className="text-xs text-[#5f7182]">{cour.name_entreprise}</span>
                            </div>
                            <div className="flex-1 text-center">
                                <h2 className="text-xl font-bold text-[#0f172a]">Fiche d'encaissement de loyers</h2>
                                <p className="mt-1 text-sm text-[#0f172a]">
                                    Nom du bailleur : <span className="font-semibold">{cour.proprietaire_nom}</span>
                                </p>
                                <p className="text-sm text-[#0f172a]">
                                    Cours : <span className="font-semibold">{cour.nom}</span>
                                </p>
                            </div>
                            <div className="flex flex-col items-center rounded-lg border border-[#0f172a] px-4 py-2 text-center">
                                <span className="text-xs font-semibold text-[#0f172a]">Période :</span>
                                <span className="text-sm font-bold text-[#0f172a]">
                                    {fmtDate(cour.periode?.debut)} - {fmtDate(cour.periode?.fin)}
                                </span>
                            </div>
                        </div>

                        {/* Tableau détaillé — mêmes colonnes que la fiche active */}
                        <div className="overflow-x-auto">
                            <table className="w-full border-collapse">
                                <thead>
                                    <tr>
                                        <th rowSpan={2} className={headCell}>N° Porte</th>
                                        <th rowSpan={2} className={headCell}>Nom et prénom des locataires</th>
                                        <th colSpan={2} className={headCell}>Situation des locataires</th>
                                        <th rowSpan={2} className={headCell}>Date de<br />paiement</th>
                                        <th rowSpan={2} className={headCell}>Montant du<br />loyer</th>
                                        <th rowSpan={2} className={headCell}>Arriérés</th>
                                        <th rowSpan={2} className={headCell}>Montant<br />attendu</th>
                                        <th colSpan={2} className={headCell}>Loyer payé en avance</th>
                                        <th rowSpan={2} className={headCell}>Nouvelle<br />caution</th>
                                        <th rowSpan={2} className={headCell}>Loyer payé</th>
                                        <th rowSpan={2} className={headCell}>Arriéré payé</th>
                                        <th rowSpan={2} className={headCell}>Caution<br />SODECI et/ou<br /> CIE</th>
                                        <th rowSpan={2} className={headCell}>Total payé</th>
                                        <th rowSpan={2} className={headCell}>Impayés</th>
                                        <th rowSpan={2} className={headCell}>Numéro de tel</th>
                                        <th rowSpan={2} className={headCell}>N° reçu</th>
                                    </tr>
                                    <tr>
                                        <th className={headCell}>Date d'entrée</th>
                                        <th className={headCell}>Caution payée</th>
                                        <th className={headCell}>Nom du mois</th>
                                        <th className={headCell}>Montant payé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {(cour.locataires || []).map((l, idx) => (
                                        <tr key={`${l.porte_id}-${l.locataire_id}-${idx}`} className="odd:bg-white even:bg-[#f7fbfe]">
                                            <td className={cell}>{l.porte}</td>
                                            <td className={cn(cell, 'font-medium text-[#0f172a]')}>{l.nom}</td>
                                            <td className={cn(cell, 'text-center')}>{fmtDate(l.dateEntree)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.cautionPayee)}</td>
                                            <td className={cn(cell, 'text-center')}>{l.datePaiement ? fmtDate(l.datePaiement) : '-'}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.montantLoyer)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.arrieres)}</td>
                                            <td className={cn(cell, 'text-right font-semibold')}>{fmtNombre(l.montantAttendu)}</td>
                                            <td className={cn(cell, 'text-center')}>
                                                {Array.isArray(l.mois_payer) ? l.mois_payer.join(' , ') : (l.mois_payer || '-')}
                                            </td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.avance)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.nouvelleCaution)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.loyerPaye)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.arrierePaye)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.cautionSodeci)}</td>
                                            <td className={cn(cell, 'text-right font-semibold text-[#4d8500]')}>{fmtNombre(l.totalPaye)}</td>
                                            <td className={cn(cell, 'text-right font-semibold', l.restant > 0 ? 'text-[#b42318]' : 'text-[#5f7182]')}>
                                                {fmtNombre(l.restant)}
                                            </td>
                                            <td className={cell}>{l.tel}</td>
                                            <td className={cn(cell, 'text-center')}>{l.numeroRecu || '-'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                <tr className="bg-[#eef3f7] font-semibold text-[#0f172a]">
                                    <td colSpan={5} className={cell}>TOTAUX</td>
                                    
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.montantLoyer)}</td>
                                    <td className={cn(cell, 'text-right')}> {fmtNombre(t.montantArrieresPaye)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.attendu)}</td>
                                    <td className={cell}></td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.avance)}</td>
                                    <td className={cn(cell, 'text-right')}> {fmtNombre(t.montantNouvelleCaution)}</td>
                                    <td className={cn(cell, 'text-right')}> {fmtNombre(t.montantLoyerPaye)}</td>
                                    <td className={cn(cell, 'text-right')}> {fmtNombre(t.montantarrierePaye)}</td>
                                    <td className={cn(cell, 'text-right')}> {fmtNombre(t.montantCautionSodeci)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.totalPaye)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.restant)}</td>
                                    <td className={cell} colSpan={2}></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        {/* Encadré récapitulatif + observation */}
                         
                            <div className="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="overflow-hidden rounded-lg border border-[#0f172a]">
                                    <table className="w-full text-sm">
                                        <tbody>
                                            <tr className="border-b border-[#0f172a]">
                                                <td className="px-3 py-2 font-medium text-[#0f172a]">TOTAL LOYER ENCAISSE</td>
                                                <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(cour.totaux?.totalEncaisse)} FCFA</td>
                                            </tr>
                                            <tr className="border-b border-[#0f172a]">
                                                <td className="px-3 py-2 font-medium text-[#0f172a]">
                                                    COMMISSION ({Math.round((cour.commissionRate || 0.10) * 100)}%)
                                                </td>
                                                <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(cour.totaux?.commission)} FCFA</td>
                                            </tr>
                                            <tr className="border-b border-[#0f172a]">
                                                <td className="px-3 py-2 font-medium text-[#0f172a]">MONTANT APRES COMMISSION</td>
                                                <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(cour.totaux?.apresCommission)} FCFA</td>
                                            </tr>
                                            <tr className="border-b border-[#0f172a]">
                                                <td className="px-3 py-2 font-medium text-[#0f172a]">NOUVELLE CAUTION</td>
                                                <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(cour.nouvelleCaution)} FCFA</td>
                                            </tr>
                                            <tr className="border-b border-[#0f172a]">
                                                <td className="px-3 py-2 font-medium text-[#0f172a]">DEPENSES EFFECTUEES</td>
                                                <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(cour.depenses)} FCFA</td>
                                            </tr>
                                            <tr className="bg-[#4d8500]/10">
                                                <td className="px-3 py-2 font-bold text-[#0f172a]">NET A REVERSER</td>
                                                <td className="px-3 py-2 text-right text-base font-bold text-[#4d8500]">{fmtNombre(cour.totaux?.net)} FCFA</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div>
                                    <h4 className="mb-2 text-sm font-medium text-[#0f172a]">Observation</h4>
                                    <div className="min-h-[140px] w-full rounded-lg border border-[#0f172a] p-3 text-sm text-[#0f172a]">
                                        {cour.observation || '—'}
                                    </div>
                                </div>
                            </div>
                            <p className="mt-6 text-center text-sm font-medium text-[#0f172a]">
                                Je reconnais avoir reçu la somme de : {montantEnLettres(cour.totaux?.net)}
                            </p>
                    </div>
                </Card>
            </div>
        </AgenceLayout>
    );
}