import React, { useState, useMemo, useCallback, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    Building2,
    CheckCircle,
    Clock,
    FileText,
    Search,
    TrendingUp,
    Inbox,
    CalendarRange,
    RotateCcw,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '../../../components/ui/dropdown-menu';
import { Badge } from '../../../components/ui/badge';
import { Input } from '../../../components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../../../components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../components/ui/table';
import { cn } from '../../../lib/utils';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

// ============================================================
// UTILITAIRES
// ============================================================
const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const fmtDate = (iso) => {
    if (!iso) return '—';
    
    const date = new Date(iso);
    if (isNaN(date.getTime())) return '—';
    
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    
    return `${day}/${month}/${year}`;
};

const today = () => {
    const d = new Date();
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
};

const toInputDate = (date) => date.toISOString().slice(0, 10);

const fmtNombre = (value) => new Intl.NumberFormat('fr-FR').format(Math.round(Number(value ?? 0)));

// Conversion d'un montant entier en toutes lettres (français), pour la mention
// "Je reconnais avoir reçu la somme de : ... FCFA" de la fiche imprimable.
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

// ============================================================
// COMPOSANT PRINCIPAL
// ============================================================
export default function ReversementIndex({
    proprietaires = [],
    cours = [],
    filters = {},
    statistics = null,
    agenceId = null,
    hasSearched = false,
}) {
    const [view, setView] = useState('dashboard');
    const [ficheCourId, setFicheCourId] = useState(null);

    // Champs de recherche : soit un propriétaire, soit une période complète (debut ET fin)
    const [proprietaireId, setProprietaireId] = useState(filters.proprietaire || 'all');
    const [dateDebut, setDateDebut] = useState(filters.date_debut || '');
    const [dateFin, setDateFin] = useState(filters.date_fin || '');

    // Filtre rapide côté client, uniquement pour affiner ce qui est déjà affiché
    // (ne déclenche jamais de recherche serveur, ne conditionne jamais l'état vide initial)
    const [quickFilter, setQuickFilter] = useState('');

    const [coursData, setCoursData] = useState(cours);

    useEffect(() => {
        setCoursData(cours);
    }, [cours]);

    const rechercheValide = useMemo(() => {
        return (proprietaireId && proprietaireId !== 'all') || Boolean(dateDebut && dateFin);
    }, [proprietaireId, dateDebut, dateFin]);

    const lancerRecherche = useCallback((overrides = {}) => {
        const proprietaire = overrides.proprietaire ?? proprietaireId;
        const debut = overrides.date_debut ?? dateDebut;
        const fin = overrides.date_fin ?? dateFin;

        router.get(
            window.location.pathname,
            {
                proprietaire: proprietaire && proprietaire !== 'all' ? proprietaire : undefined,
                date_debut: debut || undefined,
                date_fin: fin || undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, [proprietaireId, dateDebut, dateFin]);

    const handleSubmitRecherche = (e) => {
        e.preventDefault();
        if (!rechercheValide) return;
        lancerRecherche();
    };

    const handleReset = () => {
        const now = new Date();
        const debut = toInputDate(new Date(now.getFullYear(), now.getMonth(), 1));
        const fin = toInputDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));

        setProprietaireId('all');
        setDateDebut(debut);
        setDateFin(fin);
        setQuickFilter('');
        router.get(window.location.pathname, {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    const handleQuickMonth = () => {
        const now = new Date();
        const debut = toInputDate(new Date(now.getFullYear(), now.getMonth(), 1));
        const fin = toInputDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));
        setDateDebut(debut);
        setDateFin(fin);
        lancerRecherche({ date_debut: debut, date_fin: fin });
    };

    const openFiche = useCallback((courId) => {
        setFicheCourId(courId);
        setView('fiche');
    }, []);

    const backToDashboard = useCallback(() => {
        setView('dashboard');
        setFicheCourId(null);
    }, []);

    // Seuls les champs "manuels" restent modifiables : nouvelle caution, dépenses, observation.
    // Le montant payé est calculé côté serveur à partir des transactions réelles, il n'est plus éditable ici.
    const updateCour = useCallback((courId, updates) => {
        setCoursData(prev =>
            prev.map(c => c.id === courId ? { ...c, ...updates } : c)
        );
    }, []);

    const propNom = (id) => {
        const p = proprietaires.find(p => p.id === id);
        return p ? p.nom : '—';
    };

    const propTel = (id) => {
        const p = proprietaires.find(p => p.id === id);
        return p ? p.tel : '';
    };

    /**
     * Totaux d'un cours.
     * Règles :
     * - attendu   = somme des montants attendus (mt_loyer des portes occupées)
     * - totalPaye = somme des montants réellement payés (transactions loyer, non reversées, période)
     * - restant   = somme des restants par locataire (attendu - payé si positif, sinon 0)
     * - avance    = somme des avances par locataire (payé - attendu si positif, sinon 0)
     * Jamais de valeur négative : le clamp est déjà fait par locataire en amont (backend).
     */
    const courTotals = (c) => {
        if (c.ficheType === 'vente' && c.vente) {
            const attendu = Number(c.vente.prixVente || 0);
            const totalPaye = Number(c.vente.montantVersePeriode || 0);
            const restant = Number(c.vente.montantRestant || 0);
            return {
                attendu,
                totalPaye,
                restant,
                avance: 0,
                pct: attendu > 0
                    ? Math.min(100, Math.max(0, Math.round((Number(c.vente.totalVerse || 0) / attendu) * 100)))
                    : 0,
            };
        }
        let attendu = 0, totalPaye = 0, restant = 0, avance = 0;
        // Détail supplémentaire utilisé par la fiche imprimable (voir FicheView)
        let montantLoyer = 0, arrieres = 0, loyerPaye = 0, arrierePaye = 0, cautionSodeci = 0, nouvelleCautionLoc = 0, fraisDossier = 0;
        //let loyer_encaisser= 0;

        c.locataires?.forEach(l => {
            attendu += l.montantAttendu || 0;
            totalPaye += l.montantPaye || 0;
            restant += l.restant || 0;
            avance += l.avance || 0;

            montantLoyer += l.montantLoyer ?? 0;
            arrieres += l.arrieres || 0;
            loyerPaye += l.loyerPaye ?? 0;
            arrierePaye += l.arrierePaye || 0;
            cautionSodeci += l.cautionSodeci || 0;
            nouvelleCautionLoc += l.cautionPayee || 0;
            fraisDossier += l.fraisDossier || 0;
        });
        const loyerEncaisser = totalPaye - (cautionSodeci + nouvelleCautionLoc + fraisDossier);
        const commission = loyerEncaisser * (c.commissionRate || 0.10);
        const apresCommission = loyerEncaisser - commission;
        const montantMaintenances = Number(c.montantMaintenances || 0);
        const net = apresCommission + (nouvelleCautionLoc || 0) + (cautionSodeci || 0)
            - (c.depenses || 0) - montantMaintenances;
        // Le taux représente uniquement la dette couverte. Les cautions,
        // frais et avances ne doivent pas produire un pourcentage supérieur.
        const montantCouvert = Math.max(attendu - restant, 0);
        const pct = attendu > 0
            ? Math.min(100, Math.max(0, Math.round((montantCouvert / attendu) * 100)))
            : 0;

        return {
            attendu, totalPaye, restant, avance, commission, apresCommission, net, pct,
            montantLoyer, arrieres, loyerPaye, arrierePaye, cautionSodeci, nouvelleCautionLoc,
            loyerEncaisser, montantMaintenances, fraisDossier
        };
    };

    // Affinage purement local (nom du cours), n'affecte jamais la visibilité initiale des données
    const displayedCours = useMemo(() => {
        if (!quickFilter.trim()) return coursData;
        const q = quickFilter.trim().toLowerCase();
        return coursData.filter(c =>
            c.nom?.toLowerCase().includes(q) ||
            propNom(c.proprietaireId).toLowerCase().includes(q)
        );
    }, [coursData, quickFilter]);

    const totals = useMemo(() => {
        let totalAttendu = 0, totalPaye = 0, totalRestant = 0, totalAvance = 0;
        displayedCours.forEach(c => {
            const t = courTotals(c);
            totalAttendu += t.attendu;
            totalPaye += t.totalPaye;
            totalRestant += t.restant;
            totalAvance += t.avance;
        });
        return { totalAttendu, totalPaye, totalRestant, totalAvance };
    }, [displayedCours]);

    // Rendu de la fiche détaillée
    if (view === 'fiche' && ficheCourId) {
        const cour = coursData.find(c => c.id === ficheCourId);
        if (!cour) return null;

        return (
            <AgenceLayout title="Fiche de reversement">
                <Head title="Fiche de reversement" />
                <FicheView
                    cour={cour}
                    onBack={backToDashboard}
                    onUpdateCour={updateCour}
                    courTotals={courTotals}
                    propNom={propNom}
                />
            </AgenceLayout>
        );
    }

    return (
        <AgenceLayout title="Reversement des loyers">
            <Head title="Reversement des loyers" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                {/* En-tête */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Reversement des loyers</h2>
                        <p className="text-sm text-[#5f7182]">
                            Gestion des reversements de loyers aux propriétaires
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                       
                        <Button  variant="outline" className={agenceButtonStyles.outline}  onClick={() => router.visit('/agence/reversement/historique')}>
                            <FileText className="h-4 w-4" />
                            Historique de reversements
                        </Button>
                     
                        {/* <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" className={agenceButtonStyles.outline}>
                                    Actions
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-56">
                                <DropdownMenuItem>Exporter en PDF</DropdownMenuItem>
                                <DropdownMenuItem>Exporter en Excel</DropdownMenuItem>
                                <DropdownMenuItem>Imprimer</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu> */}
                    </div>
                </div>

                {/* Statistiques globales de l'agence (toutes périodes confondues) */}
                {statistics && <GlobalStats statistics={statistics} />}

                {/* Barre de recherche : propriétaire OU période complète */}
                <SearchBar
                    proprietaires={proprietaires}
                    proprietaireId={proprietaireId}
                    dateDebut={dateDebut}
                    dateFin={dateFin}
                    rechercheValide={rechercheValide}
                    onProprietaireChange={setProprietaireId}
                    onDateDebutChange={setDateDebut}
                    onDateFinChange={setDateFin}
                    onSubmit={handleSubmitRecherche}
                    onReset={handleReset}
                    onQuickMonth={handleQuickMonth}
                />

                {/* Résultats */}
                {!hasSearched ? (
                    <EmptyState onQuickMonth={handleQuickMonth} />
                ) : (
                    <>
                        <Stats
                            totalAttendu={totals.totalAttendu}
                            totalPaye={totals.totalPaye}
                            totalRestant={totals.totalRestant}
                            totalAvance={totals.totalAvance}
                            nbCours={displayedCours.length}
                        />

                        {coursData.length > 0 && (
                            <div className="flex items-center gap-2">
                                <div className="relative w-full max-w-sm">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#5f7182]" />
                                    <Input
                                        type="text"
                                        placeholder="Filtrer les résultats affichés..."
                                        value={quickFilter}
                                        onChange={(e) => setQuickFilter(e.target.value)}
                                        className="h-10 rounded-xl border-[#c8d4de] pl-9"
                                    />
                                </div>
                            </div>
                        )}

                        <ReversementList
                            cours={displayedCours}
                            onOpenFiche={openFiche}
                            propNom={propNom}
                            propTel={propTel}
                            courTotals={courTotals}
                        />
                    </>
                )}
            </div>
        </AgenceLayout>
    );
}

// ============================================================
// COMPOSANT STATISTIQUES GLOBALES (agence, toutes périodes)
// ============================================================
function GlobalStats({ statistics }) {
    const items = [
        { label: 'Reversements', value: statistics.total_reversements ?? 0 },
        { label: 'En attente', value: statistics.en_attente ?? 0 },
        { label: 'Reversés', value: statistics.reverses ?? 0 },
        { label: 'Annulés', value: statistics.annules ?? 0 },
        { label: 'Total attendu', value: currency(statistics.total_attendu) },
        { label: 'Net à reverser', value: currency(statistics.total_net_a_reverser) },
    ];

    return (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            {items.map((item) => (
                <div key={item.label} className="rounded-xl border border-[#c8d4de] bg-white p-3 shadow-sm">
                    <p className="text-[11px] font-medium uppercase tracking-wide text-[#5f7182]">{item.label}</p>
                    <p className="mt-1 text-base font-semibold text-[#0f172a]">{item.value}</p>
                </div>
            ))}
        </div>
    );
}

// ============================================================
// COMPOSANT BARRE DE RECHERCHE
// ============================================================
function SearchBar({
    proprietaires,
    proprietaireId,
    dateDebut,
    dateFin,
    rechercheValide,
    onProprietaireChange,
    onDateDebutChange,
    onDateFinChange,
    onSubmit,
    onReset,
    onQuickMonth,
}) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base text-[#0f172a]">
                    <CalendarRange className="h-4 w-4 text-[#00559b]" />
                    Rechercher un reversement
                </CardTitle>
                <CardDescription className="text-sm text-[#5f7182]">
                    Choisissez un propriétaire, ou une période complète (date de début ET date de fin).
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={onSubmit} className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="flex flex-col gap-2">
                        <label className="text-sm font-medium text-[#0f172a]">Propriétaire</label>
                        <Select value={proprietaireId} onValueChange={onProprietaireChange}>
                            <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                <SelectValue placeholder="Tous les propriétaires" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tous les propriétaires</SelectItem>
                                {proprietaires.map((p) => (
                                    <SelectItem key={p.id} value={p.id}>
                                        {p.nom}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="flex flex-col gap-2">
                        <label className="text-sm font-medium text-[#0f172a]">Date de début</label>
                        <Input
                            type="date"
                            value={dateDebut}
                            onChange={(e) => onDateDebutChange(e.target.value)}
                            className="h-11 rounded-xl border-[#c8d4de]"
                        />
                    </div>

                    <div className="flex flex-col gap-2">
                        <label className="text-sm font-medium text-[#0f172a]">Date de fin</label>
                        <Input
                            type="date"
                            value={dateFin}
                            onChange={(e) => onDateFinChange(e.target.value)}
                            className="h-11 rounded-xl border-[#c8d4de]"
                        />
                    </div>

                    <div className="flex items-end gap-2">
                        <Button
                            type="submit"
                            disabled={!rechercheValide}
                            className={cn(agenceButtonStyles.primary, 'flex-1')}
                        >
                            <Search className="mr-2 h-4 w-4" />
                            Rechercher
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            className={agenceButtonStyles.outline}
                            onClick={onReset}
                            title="Réinitialiser"
                        >
                            <RotateCcw className="h-4 w-4" />
                        </Button>
                    </div>
                </form>

                {!rechercheValide && (
                    <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <p className="text-xs text-[#5f7182]">
                            Sélectionnez un propriétaire, ou une date de début ET une date de fin, pour activer la recherche.
                        </p>
                        <Button variant="outline" className={agenceButtonStyles.outline} onClick={onQuickMonth} type="button">
                            Voir le mois en cours
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

// ============================================================
// COMPOSANT ÉTAT VIDE (avant toute recherche)
// ============================================================
function EmptyState({ onQuickMonth }) {
    return (
        <Card className="rounded-2xl border-dashed border-[#c8d4de] bg-[#f7fbfe] shadow-none">
            <CardContent className="flex flex-col items-center justify-center py-16 text-center">
                <span className="flex h-14 w-14 items-center justify-center rounded-full bg-[#eaf4fb] text-[#00559b]">
                    <Inbox className="h-7 w-7" />
                </span>
                <p className="mt-4 text-base font-semibold text-[#0f172a]">
                    Lancez une recherche pour voir les reversements
                </p>
                <p className="mt-1 max-w-sm text-sm text-[#5f7182]">
                    Choisissez un propriétaire, ou une période (date de début et date de fin), puis cliquez sur « Rechercher ».
                </p>
                <Button className={cn(agenceButtonStyles.primary, 'mt-5')} onClick={onQuickMonth} type="button">
                    Voir le mois en cours
                </Button>
            </CardContent>
        </Card>
    );
}

// ============================================================
// COMPOSANT STATS (résultats de la recherche en cours)
// ============================================================
function Stats({ totalAttendu, totalPaye, totalRestant, totalAvance, nbCours }) {
    const stats = [
        {
            label: 'Total attendu',
            value: currency(totalAttendu),
            icon: TrendingUp,
            accent: 'bg-[#eaf4fb] text-[#00559b]'
        },
        {
            label: 'Total payé',
            value: currency(totalPaye),
            icon: CheckCircle,
            accent: 'bg-[#eef8df] text-[#4d8500]'
        },
        {
            label: 'Total restant',
            value: currency(totalRestant),
            icon: Clock,
            accent: 'bg-[#fdecec] text-[#b42318]'
        },
        {
            label: 'Total avance',
            value: currency(totalAvance),
            icon: TrendingUp,
            accent: 'bg-[#eaf4fb] text-[#00559b]'
        },
        {
            label: 'Nombre de cours',
            value: nbCours,
            icon: Building2,
            accent: 'bg-[#f1f5f9] text-[#5f7182]'
        },
    ];

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            {stats.map((stat) => {
                const Icon = stat.icon;
                return (
                    <Card key={stat.label} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardDescription className="text-sm font-medium text-[#5f7182]">
                                {stat.label}
                            </CardDescription>
                            <span className={cn('flex h-10 w-10 items-center justify-center rounded-xl', stat.accent)}>
                                <Icon className="h-5 w-5" />
                            </span>
                        </CardHeader>
                        <CardContent>
                            <p className="text-xl font-semibold text-[#0f172a]">{stat.value}</p>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}

// ============================================================
// COMPOSANT LISTE DES REVERSEMENTS
// ============================================================
function ReversementList({ cours, onOpenFiche, propNom, propTel, courTotals }) {
    if (cours.length === 0) {
        return (
            <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                <CardContent className="flex flex-col items-center justify-center py-12">
                    <Building2 className="h-12 w-12 text-[#c8d4de]" />
                    <p className="mt-4 text-sm font-medium text-[#0f172a]">Aucun cours trouvé</p>
                    <p className="text-sm text-[#5f7182]">Aucun lot ne correspond à cette recherche.</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader>
                <CardTitle className="text-base text-[#0f172a]">Liste des biens</CardTitle>
                <CardDescription className="text-sm text-[#5f7182]">
                    {cours.length} cours trouvé(s)
                </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
                <div className="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow className="border-b border-[#eef3f7]">
                                <TableHead className="text-xs font-medium text-[#5f7182]">Cours</TableHead>
                                <TableHead className="text-xs font-medium text-[#5f7182]">Propriétaire</TableHead>
                                <TableHead className="text-right text-xs font-medium text-[#5f7182]">Attendu</TableHead>
                                <TableHead className="text-right text-xs font-medium text-[#5f7182]">Payé</TableHead>
                                <TableHead className="text-right text-xs font-medium text-[#5f7182]">Restant</TableHead>
                                <TableHead className="text-right text-xs font-medium text-[#5f7182]">Avance</TableHead>
                                <TableHead className="text-center text-xs font-medium text-[#5f7182]">Statut</TableHead>
                                <TableHead className="text-right text-xs font-medium text-[#5f7182]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {cours.map((c) => {
                                const t = courTotals(c);
                                return (
                                    <TableRow key={c.id} className="hover:bg-[#f7fbfe]">
                                        <TableCell>
                                            <div className="font-medium text-[#0f172a]">{c.nom}</div>
                                            <div className="text-xs text-[#5f7182]">
                                                {c.ficheType === 'vente'
                                                    ? `Vente · ${c.vente?.acheteur?.nom || 'Acheteur non renseigné'}`
                                                    : `${c.locataires?.length || 0} locataire(s)`}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="text-sm text-[#0f172a]">{propNom(c.proprietaireId)}</div>
                                            <div className="text-xs text-[#5f7182]">{propTel(c.proprietaireId)}</div>
                                        </TableCell>
                                        <TableCell className="text-right font-medium text-[#0f172a]">
                                            {currency(t.attendu)}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="font-medium text-[#4d8500]">{currency(t.totalPaye)}</div>
                                            <div className="mt-1 h-1.5 w-full rounded-full bg-[#eef3f7]">
                                                <div
                                                    className="h-1.5 rounded-full bg-[#4d8500] transition-all"
                                                    style={{ width: `${Math.min(t.pct, 100)}%` }}
                                                />
                                            </div>
                                            <div className="text-xs text-[#5f7182]">{t.pct}% payé</div>
                                        </TableCell>
                                        <TableCell className="text-right font-medium text-[#b42318]">
                                            {currency(t.restant)}
                                        </TableCell>
                                        <TableCell className="text-right font-medium text-[#00559b]">
                                            {currency(t.avance)}
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <Badge
                                                variant={
                                                    c.statut === 'reverse'
                                                        ? 'success'
                                                        : c.statut === 'annule'
                                                            ? 'destructive'
                                                            : 'warning'
                                                }
                                                className="rounded-full px-3 py-1 text-xs font-medium"
                                            >
                                                {c.statut === 'reverse' ? 'Reversé' : c.statut === 'annule' ? 'Annulé' : 'En attente'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                className={agenceButtonStyles.actionBlueIcon}
                                                onClick={() => onOpenFiche(c.id)}
                                                title="Voir la fiche"
                                            >
                                                <FileText className="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    );
}

// ============================================================
// COMPOSANT FICHE — reproduit la "Fiche d'encaissement de loyers" imprimée
// ============================================================
// Champs attendus par locataire pour que la fiche soit complète (au-delà de
// ceux déjà utilisés sur le tableau de bord) :
//   date_entree, caution_payee, date_paiement, montant_loyer, arrieres,
//   avance_mois, avance_montant, nouvelle_caution_locataire, loyer_paye,
//   arriere_paye, caution_sodeci, numero_recu
// Tous sont lus avec des valeurs par défaut (0 / '—') si absents pour ne pas
// casser l'affichage tant que le backend ne les fournit pas encore.
function FicheView({ cour, onBack, onUpdateCour, courTotals, propNom }) {
    if (cour.ficheType === 'vente' && cour.vente) {
        return <FicheVenteView cour={cour} onBack={onBack} propNom={propNom} />;
    }
    const t = courTotals(cour);
    const readonly = cour.statut === 'reverse';

    const handleCourFieldChange = (field, value) => {
        onUpdateCour(cour.id, { [field]: field === 'observation' ? value : Number(value) || 0 });
    };

  const handleMarquerReverse = () => {
    if (t.restant > 0) {
        alert('Impossible de valider : il reste des impayés.');
        return;
    }
    if (!confirm('Confirmez-vous le reversement de ce cours ?')) return;

    router.post(
        `/agence/reversement/${cour.id}/marquer-reverse`,
        {
            proprietaire_id: cour.proprietaireId,
            periode_debut: cour.periode?.debut,
            periode_fin: cour.periode?.fin,
            taux_commission: cour.commissionRate || 0.10,

            // Totaux du cours (envoyés pour info/traçabilité — le backend les recalcule de toute façon)
            total_loyer_encaisse: t.loyerEncaisser || 0,
            montant_commission: t.commission || 0,
            montant_apres_commission: t.apresCommission || 0,
            nouvelle_caution: t.nouvelleCautionLoc || 0,
            caution_sodeci: t.cautionSodeci || 0,
            depenses_effectuees: cour.depenses || 0,
            frais_dossier: t.fraisDossier || 0,
            montant_maintenances: t.montantMaintenances || 0,
            total_arriere_paye : t.arrierePaye || 0,
            total_loyer_paye: t.loyerPaye ?? t.montantAttendu ?? 0,
            total_restant: t.restant || 0,
            total_loyer_attendu: t.montantLoyer || 0,
            net_a_reverser: t.net || 0,
            observation: cour.observation || '',

            locataires: (cour.locataires || []).map(l => ({
                locataire_id: l.locataire_id,
                porte_id: l.porte_id,
                propriete_id: l.propriete_id ?? null,
                batiment_id: l.batiment_id ?? null,
                date_paiement: l.datePaiement ?? null,
                
                caution_payee: l.cautionPayee || 0,
                date_entree: l.dateEntree ?? null,
                montant_loyer: l.montantLoyer ?? l.montantAttendu ?? 0,
                arrieres: l.arrieres || 0,
                montant_attendu: l.montantAttendu || 0,
                mois_payer: l.mois_payer ?? [],
                 montant_paye: l.avance || 0,
                nouvelle_caution: l.cautionPayee || 0,
                loyer_paye: l.loyerPaye ?? l.montantPaye ?? 0,
                arriere_paye: l.arrierePaye || 0,
                caution_sodeci: l.cautionSodeci || 0,
                frais_dossier: l.fraisDossier || 0,
                total_paye: l.montantPaye || 0,
                restant: l.impayes || 0,
            })),
        },
        { preserveScroll: true }
    );
};

    const handleRouvrirFiche = () => {
        onUpdateCour(cour.id, { statut: 'en_attente' });
    };

    const cell = 'border border-[#dbe3ea] px-2 py-1.5 text-[11px] leading-tight';
    const headCell = cn(cell, 'bg-[#f1f5f9] text-center font-semibold text-[#0f172a]');

    return (
        <div className="mx-auto flex w-full max-w-[1400px] flex-col gap-6">
            <Button
                variant="outline"
                className="w-fit gap-2 border-[#c8d4de] print:hidden"
                onClick={onBack}
            >
                ← Retour au tableau de bord
            </Button>

            <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm print:border-0 print:shadow-none">
                {/* Bannière de statut + actions (masquées à l'impression) */}
                <div className="flex flex-col gap-3 p-6 print:hidden">
                    <div className={cn(
                        'rounded-xl px-4 py-3 text-sm font-medium',
                        cour.statut === 'reverse'
                            ? 'bg-[#eef8df] text-[#4d8500]'
                            : cour.statut === 'annule'
                                ? 'bg-[#fdecec] text-[#b42318]'
                                : 'bg-[#fef3e2] text-[#d97706]'
                    )}>
                        {cour.statut === 'reverse'
                            ? '✓ Ce reversement a déjà été effectué au bailleur.'
                            : cour.statut === 'annule'
                                ? '✕ Ce reversement a été annulé.'
                                : '⚠ Reversement en attente — les montants payés sont calculés automatiquement à partir des transactions enregistrées.'
                        }
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {/* <Button variant="outline" className={agenceButtonStyles.outline} onClick={() => window.print()}>
                            🖨️ Imprimer
                        </Button> */}
                        {!readonly ? (
                            <Button
                                className={agenceButtonStyles.primary}
                                onClick={handleMarquerReverse}
                                disabled={t.restant > 0}
                            >
                                <CheckCircle className="mr-2 h-4 w-4" />
                                Marquer comme reversé
                            </Button>
                        ) : (
                            <Button variant="outline" className={agenceButtonStyles.outline} onClick={handleRouvrirFiche}>
                                ↺ Rouvrir la fiche
                            </Button>
                        )}
                    </div>
                </div>

                {/* ===================== Contenu imprimable ===================== */}
                <div className="relative isolate overflow-hidden p-6">
                    {cour.logo_entreprise ? (
                        <img
                            src={cour.logo_entreprise}
                            alt=""
                            className="pointer-events-none absolute left-1/2 top-1/2 -z-10 max-h-[65%] w-[42%] -translate-x-1/2 -translate-y-1/2 object-contain opacity-[0.07]"
                        />
                    ) : null}
                    {/* En-tête du document */}
                    <div className="mb-4 flex items-start justify-between gap-4">
                       <div className="flex items-center gap-3">
                            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-[#00559b] text-sm font-bold text-white">
                                <img src={cour.logo_entreprise} alt="" srcSet="" />
                            </div>
                            <span className="text-xs text-[#5f7182]">{cour.name_entreprise}</span>
                        </div>
                        <div className="flex-1 text-center">
                            <h2 className="text-xl font-bold text-[#0f172a]">Fiche d'encaissement de loyers</h2>
                            <p className="mt-1 text-sm text-[#0f172a]">
                                Nom du bailleur : <span className="font-semibold">{propNom(cour.proprietaireId)}</span>
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

                    {/* Tableau détaillé, colonnes identiques à la fiche imprimée */}
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
                                {cour.locataires?.map((l, idx) => {
                                    const montantLoyer = l.montantLoyer ?? l.montantAttendu ?? 0;
                                    const arrieres = l.arrieres || 0;
                                    const attendu = montantLoyer + arrieres;
                                    const loyerPaye = l.loyerPaye ?? l.montantPaye ?? 0;
                                    const arrierePaye = l.arrierePaye || 0;
                                    const totalPaye = l.montantPaye ?? (loyerPaye + arrierePaye);
                                    const impayes = l.restant ?? Math.max(attendu - (loyerPaye + arrierePaye), 0);

                                    return (
                                        <tr key={`${l.porte_id}-${l.locataire_id}-${idx}`} className="odd:bg-white even:bg-[#f7fbfe]">
                                            <td className={cell}>{l.porte}</td>
                                            <td className={cn(cell, 'font-medium text-[#0f172a]')}>{l.nom}</td>
                                            <td className={cn(cell, 'text-center')}>{fmtDate(l.dateEntree) || '—'}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.cautionPayee)}</td>
                                            <td className={cn(cell, 'text-center')}>{l.datePaiement ? fmtDate(l.datePaiement) : '-'}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(montantLoyer)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(arrieres)}</td>
                                            <td className={cn(cell, 'text-right font-semibold')}>{fmtNombre(attendu)}</td>
                                            <td className={cn(cell, 'text-center')}>
                                                {Array.isArray(l.mois_payer) 
                                                    ? l.mois_payer.join(' , ') 
                                                    : l.mois_payer || '-'}
                                            </td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.avance)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.cautionPayee)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(loyerPaye)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(arrierePaye)}</td>
                                            <td className={cn(cell, 'text-right')}>{fmtNombre(l.cautionSodeci)}</td>
                                            <td className={cn(cell, 'text-right font-semibold text-[#4d8500]')}>{fmtNombre(totalPaye)}</td>
                                            <td className={cn(cell, 'text-right font-semibold', impayes > 0 ? 'text-[#b42318]' : 'text-[#5f7182]')}>
                                                {fmtNombre(impayes)}
                                            </td>
                                            <td className={cell}>{l.tel}</td>
                                            <td className={cn(cell, 'text-center')}>{l.numeroRecu || '-'}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                            <tfoot>
                                <tr className="bg-[#eef3f7] font-semibold text-[#0f172a]">
                                    <td colSpan={5} className={cell}>TOTAUX</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.montantLoyer)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.arrieres)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.attendu)}</td>
                                    <td className={cell}></td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.avance)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.nouvelleCautionLoc)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.loyerPaye)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.arrierePaye)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.cautionSodeci)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.totalPaye)}</td>
                                    <td className={cn(cell, 'text-right')}>{fmtNombre(t.restant)}</td>
                                    <td className={cell} colSpan={2}></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {/* Encadré récapitulatif + observation, comme sur la fiche imprimée */}
                    <div className="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div className="overflow-hidden rounded-lg border border-[#0f172a]">
                            <table className="w-full text-sm">
                                <tbody>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">TOTAL LOYER ENCAISSE</td>
                                        <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(t.loyerEncaisser )} FCFA</td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">
                                            COMMISSION ({Math.round((cour.commissionRate || 0.10) * 100)}%)
                                        </td>
                                        <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(t.commission)} FCFA</td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">MONTANT APRES COMMISSION</td>
                                        <td className="px-3 py-2 text-right font-semibold text-[#0f172a]">{fmtNombre(t.apresCommission)} FCFA</td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">NOUVELLE CAUTION</td>
                                        <td className="px-3 py-2 text-right">
                                            <input
                                                type="number"
                                                min="0"
                                                readOnly
                                                value={t.nouvelleCautionLoc || 0}
                                                disabled={readonly}
                                                onChange={(e) => handleCourFieldChange('nouvelleCaution', e.target.value)}
                                                className="w-32 rounded border border-[#c8d4de] px-2 py-1 text-right text-sm focus:border-[#00559b] focus:outline-none disabled:bg-[#f7fbfe] print:border-0"
                                            />
                                        </td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">CAUTION CIE/SODECI</td>
                                        <td className="px-3 py-2 text-right">
                                            <input
                                                type="number"
                                                min="0"
                                                readOnly
                                                value={t.cautionSodeci || 0}
                                                disabled={readonly}
                                                onChange={(e) => handleCourFieldChange('cautionSodeci', e.target.value)}
                                                className="w-32 rounded border border-[#c8d4de] px-2 py-1 text-right text-sm focus:border-[#00559b] focus:outline-none disabled:bg-[#f7fbfe] print:border-0"
                                            />
                                        </td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">FRAIS DE DOSSIER (NON REVERSÉS)</td>
                                        <td className="px-3 py-2 text-right font-semibold text-[#d97706]">{fmtNombre(t.fraisDossier)} FCFA</td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">MAINTENANCES — MONTANT VERSÉ SUR LA PÉRIODE</td>
                                        <td className="px-3 py-2 text-right font-semibold text-[#b42318]">{fmtNombre(t.montantMaintenances)} FCFA</td>
                                    </tr>
                                    <tr className="border-b border-[#0f172a]">
                                        <td className="px-3 py-2 font-medium text-[#0f172a]">DEPENSES EFFECTUEES</td>
                                        <td className="px-3 py-2 text-right">
                                            <input
                                                type="number"
                                                min="0"
                                                readOnly
                                                value={cour.depenses || 0}
                                                disabled={readonly}
                                                onChange={(e) => handleCourFieldChange('depenses', e.target.value)}
                                                className="w-32 rounded border border-[#c8d4de] px-2 py-1 text-right text-sm focus:border-[#00559b] focus:outline-none disabled:bg-[#f7fbfe] print:border-0"
                                            />
                                        </td>
                                    </tr>
                                    <tr className="bg-[#4d8500]/10">
                                        <td className="px-3 py-2 font-bold text-[#0f172a]">NET A REVERSER</td>
                                        <td className="px-3 py-2 text-right text-base font-bold text-[#4d8500]">{fmtNombre(t.net)} FCFA</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <h4 className="mb-2 text-sm font-medium text-[#0f172a]">Observation</h4>
                            <textarea
                                disabled={readonly}
                                value={cour.observation || ''}
                                onChange={(e) => handleCourFieldChange('observation', e.target.value)}
                                className="min-h-[140px] w-full rounded-lg border border-[#0f172a] p-3 text-sm focus:outline-none disabled:bg-[#f7fbfe] print:border"
                                placeholder="Ajouter une observation..."
                            />
                        </div>
                    </div>

                    {/* Mention légale + signatures, comme sur la fiche imprimée */}
                    <p className="mt-6 text-center text-sm font-medium text-[#0f172a]">
                        Je reconnais avoir reçu la somme de : {montantEnLettres(t.net)}
                    </p>

               {/***  <div className="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div className="text-sm text-[#5f7182]">
                            Fait à Anyama carrefour Berthé, le <span className="font-medium text-[#0f172a]">{today()}</span>
                        </div>
                        <div className="flex gap-16 text-sm font-medium text-[#0f172a]">
                            <span>LE GESTIONNAIRE</span>
                            <span>LE BAILLEUR</span>
                        </div>
                    </div>* */} 
                </div>
            </Card>
        </div>
    );
}

export function FicheVenteView({ cour, onBack, propNom, readonly = false }) {
    const vente = cour.vente;
    const lot = cour.lot || {};
    const cell = 'border border-[#dbe3ea] px-3 py-2 text-sm';
    const headCell = cn(cell, 'bg-[#f1f5f9] font-semibold text-[#0f172a]');
    const handleReversement = () => {
        if (!Number(vente.montantVersePeriode || 0)) return;
        if (!confirm(`Confirmez-vous le reversement net de ${currency(vente.netProprietairePeriode)} au propriétaire, après la commission de l’agence ?`)) return;
        router.post(`/agence/reversement/ventes/${vente.id}/marquer-reverse`, {
            periode_debut: cour.periode?.debut,
            periode_fin: cour.periode?.fin,
        }, { preserveScroll: true });
    };

    return (
        <div className="mx-auto flex w-full max-w-[1100px] flex-col gap-6">
            <Button variant="outline" className="w-fit gap-2 border-[#c8d4de] print:hidden" onClick={onBack}>
                ← Retour au tableau de bord
            </Button>
            <Card className="relative isolate overflow-hidden rounded-2xl border-[#c8d4de] bg-white p-7 shadow-sm print:border-0 print:shadow-none">
                {cour.logo_entreprise ? <img src={cour.logo_entreprise} alt="" className="pointer-events-none absolute left-1/2 top-1/2 -z-10 w-[42%] -translate-x-1/2 -translate-y-1/2 object-contain opacity-[0.06]" /> : null}

                <div className="mb-6 flex items-start justify-between gap-5 border-b border-[#dbe3ea] pb-5">
                    <div className="flex items-center gap-3">
                        {cour.logo_entreprise ? <img src={cour.logo_entreprise} alt="Logo" className="h-14 w-14 object-contain" /> : null}
                        <div><div className="font-semibold text-[#0f172a]">{cour.name_entreprise}</div><div className="text-xs text-[#5f7182]">Fiche financière</div></div>
                    </div>
                    <div className="text-center">
                        <h2 className="text-xl font-bold text-[#0f172a]">Fiche de reversement — Vente d’un lot</h2>
                        <p className="mt-1 text-sm text-[#5f7182]">Période du {fmtDate(cour.periode?.debut)} au {fmtDate(cour.periode?.fin)}</p>
                    </div>
                    <div className="flex gap-2 print:hidden">
                        {readonly && cour.id ? (
                            <Button variant="outline" asChild>
                                <a href={`/agence/reversement/pdf/${cour.id}/${cour.periode?.debut}/${cour.periode?.fin}`}>📄 Télécharger le PDF</a>
                            </Button>
                        ) : null}
                        {!readonly ? (
                            <Button className={agenceButtonStyles.primary} disabled={!Number(vente.montantVersePeriode || 0)} onClick={handleReversement}>
                                <CheckCircle className="mr-2 h-4 w-4" />
                                Reverser au propriétaire
                            </Button>
                        ) : null}
                    </div>
                </div>

                <div className="mb-6 grid gap-4 md:grid-cols-3">
                    <InfoTile label="Propriétaire" value={propNom(cour.proprietaireId)} />
                    <InfoTile label="Acheteur" value={vente.acheteur?.nom || '—'} detail={vente.acheteur?.telephone || vente.acheteur?.email} />
                    <InfoTile label="Référence de la vente" value={vente.reference || '—'} detail={vente.dateAccord ? `Accord du ${fmtDate(vente.dateAccord)}` : ''} />
                    <InfoTile label="Lot" value={lot.nom || cour.nom} detail={[lot.ilot && `Îlot ${lot.ilot}`, lot.numero && `Lot ${lot.numero}`].filter(Boolean).join(' · ')} />
                    <InfoTile label="Adresse" value={lot.adresse || '—'} />
                    <InfoTile label="Mode de règlement" value={(vente.typePaiement || '—').replaceAll('_', ' ')} />
                </div>

                <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <AmountTile label="Montant initial du lot" value={vente.prixVente} />
                    <AmountTile label="Versé sur la période" value={vente.montantVersePeriode} accent="text-[#4d8500]" />
                    <AmountTile label="Total versé" value={vente.totalVerse} accent="text-[#00559b]" />
                    <AmountTile label="Reste à payer" value={vente.montantRestant} accent="text-[#b42318]" />
                    <AmountTile label={`Commission agence (${Number(vente.tauxAgence || 0).toLocaleString('fr-FR')} %)`} value={vente.commissionAgencePeriode} accent="text-[#d97706]" />
                    <AmountTile label="Net à reverser au propriétaire" value={vente.netProprietairePeriode} accent="text-[#4d8500]" />
                </div>
                {!readonly && !Number(vente.montantVersePeriode || 0) ? (
                    <div className="mb-5 rounded-lg bg-[#eef3f7] px-4 py-3 text-sm text-[#5f7182]">
                        Aucun versement non reversé n’est disponible sur cette période.
                    </div>
                ) : null}

                <h3 className="mb-3 font-semibold text-[#0f172a]">Versements effectués pendant la période</h3>
                <div className="overflow-x-auto">
                    <table className="w-full border-collapse">
                        <thead><tr><th className={headCell}>Rang</th><th className={headCell}>Date du versement</th><th className={headCell}>N° reçu</th><th className={cn(headCell, 'text-right')}>Montant versé</th><th className={cn(headCell, 'text-right')}>Reste après versement</th></tr></thead>
                        <tbody>
                            {vente.versements?.length ? vente.versements.map((versement, index) => (
                                <tr key={versement.id}><td className={cell}>{index + 1}</td><td className={cell}>{fmtDate(versement.date)}</td><td className={cell}>{versement.numeroRecu || '—'}</td><td className={cn(cell, 'text-right font-semibold')}>{currency(versement.montant)}</td><td className={cn(cell, 'text-right font-semibold text-[#b42318]')}>{currency(versement.resteApresVersement)}</td></tr>
                            )) : <tr><td colSpan={5} className={cn(cell, 'py-8 text-center text-[#5f7182]')}>Aucun versement enregistré pendant cette période.</td></tr>}
                        </tbody>
                        <tfoot>
                            <tr><td colSpan={4} className={cn(headCell, 'text-right')}>TOTAL VERSÉ SUR LA PÉRIODE</td><td className={cn(headCell, 'text-right')}>{currency(vente.montantVersePeriode)}</td></tr>
                            <tr><td colSpan={4} className={cn(headCell, 'text-right')}>POURCENTAGE DE L’AGENCE APPLIQUÉ AU TOTAL</td><td className={cn(headCell, 'text-right')}>{Number(vente.tauxAgence || 0).toLocaleString('fr-FR')} %</td></tr>
                            <tr><td colSpan={4} className={cn(headCell, 'text-right')}>COMMISSION DE L’AGENCE SUR LE TOTAL</td><td className={cn(headCell, 'text-right text-[#d97706]')}>{currency(vente.commissionAgencePeriode)}</td></tr>
                            <tr><td colSpan={4} className={cn(headCell, 'text-right')}>NET À REVERSER AU PROPRIÉTAIRE</td><td className={cn(headCell, 'text-right text-[#4d8500]')}>{currency(vente.netProprietairePeriode)}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </Card>
        </div>
    );
}

function InfoTile({ label, value, detail }) {
    return <div className="rounded-xl border border-[#dbe3ea] bg-[#f8fafc] p-4"><div className="text-xs font-medium uppercase tracking-wide text-[#5f7182]">{label}</div><div className="mt-1 font-semibold text-[#0f172a]">{value}</div>{detail ? <div className="mt-1 text-xs text-[#5f7182]">{detail}</div> : null}</div>;
}

function AmountTile({ label, value, accent = 'text-[#0f172a]' }) {
    return <div className="rounded-xl border border-[#c8d4de] bg-white p-4"><div className="text-xs font-medium text-[#5f7182]">{label}</div><div className={cn('mt-2 text-xl font-bold', accent)}>{currency(value)}</div></div>;
}
