import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    CalendarClock,
    CheckCircle2,
    FileText,
    Download,
    Home,
    IdCard,
    Layers,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Plus,
    Ruler,
    Trash2,
    UserRound,
    Wallet,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { Separator } from '../../../components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const asArray = (value) => (Array.isArray(value) ? value : []);
const buildEmptyLotForm = () => ({
    name: '',
    num_lot: '',
    num_ilot: '',
    superficie: '',
    adresse: '',
    region_id: '',
    ville_id: '',
    is_for_sale: false,
    sale_price: '',
});

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));
const monthKey = (year, month) => `${Number(year)}-${String(Number(month)).padStart(2, '0')}`;

const formatDate = (value) => {
    if (!value) return 'Non renseigné';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

const initials = (name) =>
    String(name ?? '')
        .split(' ')
        .filter(Boolean)
        .map((word) => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

const currentPhotoUrl = (proprietaire) => {
    if (!proprietaire?.photo) {
        return '';
    }

    if (String(proprietaire.photo).startsWith('http')) {
        return proprietaire.photo;
    }

    return `/storage/${proprietaire.photo}`;
};

const NAV_ITEMS = [
    { id: 'informations', label: 'Informations', icon: UserRound },
    { id: 'liaison', label: 'Représentant', icon: FileText },
    { id: 'lots', label: 'Lots', icon: Layers },
    { id: 'proprietes', label: 'Propriétés', icon: Building2 },
];

function StatusBadge({ active, activeLabel = 'Actif', inactiveLabel = 'Inactif' }) {
    if (active) {
        return (
            <Badge variant="outline" className="rounded-full border-[#c7e3a6] bg-[#f2f9e8] text-[#4d8500]">
                <CheckCircle2 className="mr-1 h-3 w-3" />
                {activeLabel}
            </Badge>
        );
    }
    return (
        <Badge variant="outline" className="rounded-full border-[#f4c9c4] bg-[#fdecea] text-[#b42318]">
            {inactiveLabel}
        </Badge>
    );
}

function InfoRow({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-3 py-2">
            <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1f5f9] text-[#5f7182]">
                <Icon className="h-4 w-4" />
            </span>
            <div className="min-w-0">
                <p className="text-[11px] font-medium uppercase tracking-wide text-[#94a3b8]">{label}</p>
                <p className="break-words text-sm font-medium text-[#0f172a]">{value || 'Non renseigné'}</p>
            </div>
        </div>
    );
}

function Section({ id, icon: Icon, title, description, action, children }) {
    return (
        <section id={id} className="scroll-mt-24 rounded-2xl border border-[#c8d4de] bg-white shadow-sm">
            <header className="flex flex-col gap-3 border-b border-[#e2e8f0] p-5 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3">
                    <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-[#eaf4fb] text-[#00559b]">
                        <Icon className="h-4 w-4" />
                    </span>
                    <div>
                        <h2 className="text-sm font-semibold text-[#0f172a]">{title}</h2>
                        {description ? <p className="text-xs text-[#5f7182]">{description}</p> : null}
                    </div>
                </div>
                {action}
            </header>
            <div className="p-5">{children}</div>
        </section>
    );
}

function EmptyState({ children }) {
    return (
        <div className="rounded-xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-8 text-center text-sm text-[#5f7182]">
            {children}
        </div>
    );
}

function Field({ label, required, error, className, children }) {
    return (
        <div className={cn('space-y-1.5', className)}>
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>
            {children}
            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function LotCard({ lot, onEdit, onDelete, canDelete }) {
    return (
        <div className="rounded-xl border border-[#c8d4de] bg-[#f8fafc] p-4">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold text-[#0f172a]">{lot.name || 'Lot'}</p>
                    <p className="text-xs text-[#5f7182]">
                        {lot.num_lot ? `N° lot: ${lot.num_lot}` : 'N° lot non renseigné'}
                        {lot.num_ilot ? ` · N° îlot: ${lot.num_ilot}` : ''}
                    </p>
                </div>
                <div className="flex items-center gap-1.5">
                    <Button type="button" variant="outline" size="icon" className={agenceButtonStyles.outline} onClick={onEdit} title="Modifier le lot">
                        <Pencil className="h-3.5 w-3.5" />
                    </Button>
                    {canDelete ? (
                        <Button type="button" variant="destructive" size="icon" className={agenceButtonStyles.danger} onClick={onDelete} title="Supprimer le lot">
                            <Trash2 className="h-3.5 w-3.5" />
                        </Button>
                    ) : null}
                </div>
            </div>

            <Separator className="my-3" />

            <div className="space-y-1.5 text-sm text-[#5f7182]">
                <p className="flex items-center gap-2">
                    <MapPin className="h-4 w-4 shrink-0" />
                    <span>{lot.region?.name || 'Région Non renseigné'} · {lot.ville?.name || 'Ville Non renseigné'}</span>
                </p>
                <p className="flex items-center gap-2">
                    <Ruler className="h-4 w-4 shrink-0" />
                    <span>{lot.superficie ? `${lot.superficie} m²` : 'Superficie non renseignée'}</span>
                </p>
                <p className="text-[#0f172a]">{lot.adresse || 'Adresse non renseignée'}</p>
                {lot.is_for_sale ? <p className="font-semibold text-[#4d8500]">En vente · {number(lot.sale_price)} FCFA</p> : null}
            </div>
        </div>
    );
}

function PropertyCard({ propriete }) {
    const batiments = asArray(propriete?.batiments);
    const portes = batiments.flatMap((batiment) => asArray(batiment?.portes));
    const portesOccupees = portes.filter((porte) => Boolean(porte?.is_occupe)).length;
    const portesLibres = Math.max(portes.length - portesOccupees, 0);

    return (
        <div className="rounded-xl border border-[#c8d4de] bg-[#f8fafc] p-4">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <p className="text-sm font-semibold text-[#0f172a]">{propriete.reference || 'Propriété'}</p>
                    <p className="text-xs text-[#5f7182]">{propriete.typePropriete?.name || 'Type non renseigné'}</p>
                </div>
                <div className="flex items-center gap-2">
                    <StatusBadge active={propriete.is_actif} activeLabel="Active" inactiveLabel="Inactive" />
                    <Button asChild variant="outline" size="icon" className={agenceButtonStyles.outline} title="Modifier la propriété">
                        <Link href={`/agence/proprietes/edit/${encodeURIComponent(propriete?.propriete_id ?? propriete?.id ?? '')}`}>
                            <Pencil className="h-3.5 w-3.5" />
                        </Link>
                    </Button>
                </div>
            </div>

            <Separator className="my-3" />

            <div className="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                <InfoRow icon={MapPin} label="Adresse" value={propriete.adresse_complete || propriete.lot?.adresse} />
                <InfoRow icon={Building2} label="Bâtiments" value={number(batiments.length)} />
                <InfoRow icon={Home} label="Portes" value={number(portes.length)} />
                <InfoRow icon={CheckCircle2} label="Portes libres" value={number(portesLibres)} />
                <InfoRow icon={CheckCircle2} label="Portes occupées" value={number(portesOccupees)} />
            </div>
        </div>
    );
}

export default function Show({ proprietaire, liaison = null, lots = [], proprietes = [], paiements = [], locatairesActifs = [], reversementRows = [], regions = [], villes = [], typePiece = [], genres = [] }) {
    const lotsList = asArray(lots);
    const proprietesList = asArray(proprietes);
    const [isLotModalOpen, setIsLotModalOpen] = useState(false);
    const [isSavingLot, setIsSavingLot] = useState(false);
    const [editingLot, setEditingLot] = useState(null);
    const [lotForm, setLotForm] = useState(buildEmptyLotForm());
    const [activeTab, setActiveTab] = useState('informations');
    const [showBackButton, setShowBackButton] = useState(false);
    const todayValue = new Date().toISOString().slice(0, 10);
    const [consolidationStart, setConsolidationStart] = useState(`${todayValue.slice(0, 8)}01`);
    const [consolidationEnd, setConsolidationEnd] = useState(todayValue);

    const regionOptions = useMemo(
        () => asArray(regions).map((region) => ({ value: String(region.id ?? ''), label: region.name })),
        [regions]
    );
    const typePieceOptions = useMemo(
        () => asArray(typePiece).map((piece) => ({ value: String(piece.type_pieces_id ?? piece.id ?? ''), label: piece.name })),
        [typePiece]
    );
    const genreOptions = useMemo(
        () => asArray(genres).map((genre) => ({ value: String(genre.id ?? ''), label: genre.name })),
        [genres]
    );
    const representativeGenreLabel = useMemo(
        () => genreOptions.find((option) => option.value === String(liaison?.genre_representant_id ?? ''))?.label ?? 'Non renseigné',
        [genreOptions, liaison?.genre_representant_id]
    );
    const representativeTypePieceLabel = useMemo(
        () => typePieceOptions.find((option) => option.value === String(liaison?.type_pieces_representant_id ?? ''))?.label ?? 'Non renseigné',
        [liaison?.type_pieces_representant_id, typePieceOptions]
    );
    const selectedTypePieceLabel = useMemo(
        () =>
            typePieceOptions.find(
                (option) => option.value === String(proprietaire?.type_pieces_id ?? proprietaire?.typePiece?.type_pieces_id ?? proprietaire?.typePiece?.id ?? '')
            )?.label ?? String(proprietaire?.type_pieces_id ?? proprietaire?.typePiece?.type_pieces_id ?? proprietaire?.typePiece?.id ?? 'Non renseigné'),
        [proprietaire?.typePiece?.id, proprietaire?.typePiece?.type_pieces_id, proprietaire?.type_pieces_id, typePieceOptions]
    );

    const villeOptions = useMemo(
        () =>
            asArray(villes)
                .filter((ville) => !lotForm.region_id || String(ville.region_id ?? '') === String(lotForm.region_id))
                .map((ville) => ({ value: String(ville.id ?? ''), label: ville.name, region_id: String(ville.region_id ?? '') })),
        [lotForm.region_id, villes]
    );

    const paiementsList = asArray(paiements);
    const paymentMonths = useMemo(() => {
        const current = new Date();
        return Array.from({ length: 12 }, (_, index) => {
            const date = new Date(current.getFullYear(), current.getMonth() - index, 1);
            return {
                key: monthKey(date.getFullYear(), date.getMonth() + 1),
                label: `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`,
            };
        });
    }, []);
    const paymentRows = useMemo(() => {
        const rows = new Map(asArray(locatairesActifs).map((locataire) => [
            String(locataire.locataire_id),
            {
                id: String(locataire.locataire_id),
                name: locataire.name || 'Locataire non renseigné',
                loyerNet: Number(locataire.loyer_net ?? 0),
                months: {},
                total: 0,
            },
        ]));
        const visibleMonths = new Set(paymentMonths.map((month) => month.key));
        paiementsList.forEach((paiement) => {
            const paymentDate = new Date(paiement.date_transaction);
            if (Number.isNaN(paymentDate.getTime())) return;
            const period = monthKey(paymentDate.getFullYear(), paymentDate.getMonth() + 1);
            if (!visibleMonths.has(period)) return;
            const tenantKey = String(paiement.locataire_id ?? paiement.locataire ?? 'inconnu');
            if (!rows.has(tenantKey)) return;
            const row = rows.get(tenantKey);
            const amount = Number(paiement.montant_paye ?? 0);
            row.months[period] = Number(row.months[period] ?? 0) + amount;
            row.total += amount;
        });
        return Array.from(rows.values()).sort((a, b) => a.name.localeCompare(b.name, 'fr'));
    }, [locatairesActifs, paiementsList, paymentMonths]);
    const monthTotals = useMemo(
        () => Object.fromEntries(paymentMonths.map((month) => [month.key, paymentRows.reduce((sum, row) => sum + Number(row.months[month.key] ?? 0), 0)])),
        [paymentMonths, paymentRows]
    );
    const totalLoyersPayes = paymentRows.reduce((sum, row) => sum + row.total, 0);
    const consolidatedProperties = useMemo(() => {
        const properties = new Map();
        asArray(reversementRows)
            .filter((row) => {
                const start = String(row.periode_debut ?? '').slice(0, 10);
                const end = String(row.periode_fin ?? '').slice(0, 10);
                return (!consolidationStart || end >= consolidationStart) && (!consolidationEnd || start <= consolidationEnd);
            })
            .forEach((row) => {
                const key = String(row.lot_id ?? 'sans-lot');
                const current = properties.get(key) ?? {
                    id: key,
                    reference: row.lot || 'Lot non renseigné',
                    adresse: row.adresse || '',
                    totalLoyer: 0,
                    commission: 0,
                    apresCommission: 0,
                    nouvelleCaution: 0,
                    depenses: 0,
                    montantReverse: 0,
                };
                const totalLoyer = Number(row.total_loyer ?? 0);
                current.totalLoyer += totalLoyer;
                current.commission += Number(row.montant_commission ?? 0);
                current.apresCommission += Number(row.montant_apres_commission ?? 0);
                current.nouvelleCaution += Number(row.nouvelle_caution ?? 0);
                current.depenses += Number(row.depenses_effectuees ?? 0);
                current.montantReverse += Number(row.net_a_reverser ?? 0);
                properties.set(key, current);
            });
        return Array.from(properties.values());
    }, [consolidationEnd, consolidationStart, reversementRows]);
    const consolidatedTotals = useMemo(() => consolidatedProperties.reduce((totals, row) => ({
        totalLoyer: totals.totalLoyer + row.totalLoyer,
        commission: totals.commission + row.commission,
        apresCommission: totals.apresCommission + row.apresCommission,
        nouvelleCaution: totals.nouvelleCaution + row.nouvelleCaution,
        depenses: totals.depenses + row.depenses,
        montantReverse: totals.montantReverse + row.montantReverse,
    }), { totalLoyer: 0, commission: 0, apresCommission: 0, nouvelleCaution: 0, depenses: 0, montantReverse: 0 }), [consolidatedProperties]);

    useEffect(() => {
        const scrollContainer = document.querySelector('main > div.flex-1.overflow-y-auto');
        const getScrollTop = () => (scrollContainer ? scrollContainer.scrollTop : window.scrollY);
        const updateBackButtonVisibility = () => {
            setShowBackButton(getScrollTop() > 24);
        };

        updateBackButtonVisibility();
        (scrollContainer ?? window).addEventListener('scroll', updateBackButtonVisibility, { passive: true });

        return () => (scrollContainer ?? window).removeEventListener('scroll', updateBackButtonVisibility);
    }, []);

    const openLotModal = () => {
        setEditingLot(null);
        setLotForm(buildEmptyLotForm());
        setIsLotModalOpen(true);
    };

    const openEditLotModal = (lot) => {
        setEditingLot(lot);
        setLotForm({
            name: lot?.name ?? '',
            num_lot: lot?.num_lot ?? '',
            num_ilot: lot?.num_ilot ?? '',
            superficie: lot?.superficie ?? '',
            adresse: lot?.adresse ?? '',
            region_id: String(lot?.region_id ?? ''),
            ville_id: String(lot?.ville_id ?? ''),
            is_for_sale: Boolean(lot?.is_for_sale),
            sale_price: lot?.sale_price ?? '',
        });
        setIsLotModalOpen(true);
    };

    const closeLotModal = () => {
        if (isSavingLot) return;
        setIsLotModalOpen(false);
        setEditingLot(null);
    };

    const notify = (type, message) => {
        window.dispatchEvent(new CustomEvent('agence:notify', { detail: { type, message } }));
    };

    const handleLotSubmit = async (event) => {
        event.preventDefault();

        if (!proprietaire?.proprietaire_id) {
            notify('error', 'Proprietaire introuvable.');
            return;
        }

        setIsSavingLot(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const isEditing = Boolean(editingLot?.propreietaire_lot_id);
            const lotId = editingLot?.propreietaire_lot_id ?? editingLot?.id;
            const response = await fetch(
                isEditing ? `/agence/proprietaire/lots/${lotId}` : `/agence/proprietaire/lots/${proprietaire.proprietaire_id}`,
                {
                    method: isEditing ? 'PUT' : 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(lotForm),
                }
            );

            const payload = await response.json().catch(() => ({}));

            if (!response.ok || payload.success === false) {
                const message =
                    payload?.message ||
                    (payload?.errors ? Object.values(payload.errors).flat().join(' ') : '') ||
                    "Impossible d'enregistrer le lot.";
                notify('error', message);
                return;
            }

            notify('success', payload.message ?? (isEditing ? 'Lot mis a jour avec succes.' : 'Lot cree avec succes.'));
            setIsLotModalOpen(false);
            setEditingLot(null);
            router.reload({ preserveScroll: true, preserveState: true });
        } catch (error) {
            notify('error', "Une erreur est survenue lors de l'enregistrement.");
        } finally {
            setIsSavingLot(false);
        }
    };

    const handleDeleteLot = async (lot) => {
        const lotId = lot?.propreietaire_lot_id ?? lot?.id;
        if (!lotId) {
            notify('error', 'Lot introuvable.');
            return;
        }

        if (!window.confirm('Supprimer ce lot ? Cette action est irréversible.')) {
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(`/agence/proprietaire/lots/${lotId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok || payload.success === false) {
                notify('error', payload?.message ?? 'Impossible de supprimer ce lot.');
                return;
            }

            notify('success', payload.message ?? 'Lot supprimé avec succès.');
            router.reload({ preserveScroll: true, preserveState: true });
        } catch (error) {
            notify('error', 'Une erreur est survenue lors de la suppression.');
        }
    };

    return (
        <AgenceLayout title="Détail propriétaire">
            <Head title="Détail propriétaire" />

            <div className="mb-6 flex w-full items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                    <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]" title="Retour à la liste des propriétaires">
                        <Link href="/agence/proprietaire">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">{proprietaire?.name}</h1>
                    </div>
                </div>

                <Button asChild size="sm" className={agenceButtonStyles.primary} title="Modifier le propriétaire">
                    <Link href={`/agence/proprietaire/${proprietaire?.proprietaire_id}/edit`}>
                        <Pencil className="h-4 w-4" />
                        Modifier
                    </Link>
                </Button>

            </div>

          

            <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
                {/* Colonne d'identité (sticky) */}
                <aside className="lg:sticky lg:top-6 lg:self-start">
                    <div className="rounded-2xl border border-[#c8d4de] bg-white p-5 shadow-sm">
                        <div
                            className={cn(
                                'mb-4 overflow-hidden transition-all duration-200',
                                showBackButton ? 'max-h-12 opacity-100' : 'max-h-0 opacity-0'
                            )}
                        >
                            <div className="flex items-center gap-2">
                                <Button
                                    asChild
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    className="rounded-xl border-[#c8d4de]"
                                    title="Retour à la liste des propriétaires"
                                >
                                    <Link href="/agence/proprietaire">
                                        <ArrowLeft className="h-4 w-4" />
                                    </Link>
                                </Button>

                                <Button asChild type="button" variant="default" size="icon" title="Modifier le propriétaire">
                                    <Link href={`/agence/proprietaire/${proprietaire?.proprietaire_id}/edit`}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <div className="flex flex-col items-center text-center">
                            <Avatar className="h-40 w-40 rounded-xl border border-[#c8d4de] bg-[#eaf4fb]">
                                {currentPhotoUrl(proprietaire) ? (
                                    <img
                                        src={currentPhotoUrl(proprietaire)}
                                        alt="Photo du propriétaire"
                                        className="h-full w-full rounded-xl object-cover"
                                    />
                                ) : (
                                    <AvatarFallback className="rounded-xl bg-[#eaf4fb] text-lg font-bold text-[#00559b]">
                                        {initials(proprietaire?.name)}
                                    </AvatarFallback>
                                )}
                            </Avatar>
                            <h1 className="mt-3 text-lg font-semibold text-[#0f172a]">{proprietaire?.name || 'Propriétaire'}</h1>
                          
                            <div className="mt-2">
                                <StatusBadge active={Boolean(liaison?.is_active)} />
                            </div>

                        </div>

           
                    </div>
                </aside>

                <Tabs value={activeTab} onValueChange={setActiveTab} className="min-w-0">
                    <TabsList className="mb-4 flex h-auto w-full flex-wrap justify-start gap-1 bg-[#eef2f6] p-1">
                        <TabsTrigger value="informations" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Informations</TabsTrigger>
                        <TabsTrigger value="lots" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Lots</TabsTrigger>
                        <TabsTrigger value="representant" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Représentant</TabsTrigger>
                        <TabsTrigger value="proprietes" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Propriétés</TabsTrigger>
                        <TabsTrigger value="documents" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Documents contractuels</TabsTrigger>
                        <TabsTrigger value="paiements" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Tableau recapitulatif de paiements</TabsTrigger>
                        <TabsTrigger value="consolide" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">Consolidé des loyers payés</TabsTrigger>
                    </TabsList>

                    <TabsContent value="informations" className="mt-0">
                    <Section id="informations" icon={UserRound} title="Informations du propriétaire" description="Coordonnées et pièce d'identité.">
                        <div className="grid grid-cols-1 gap-x-6 md:grid-cols-2">
                            <InfoRow icon={UserRound} label="Nom et prénom" value={proprietaire?.name} />
                            <InfoRow icon={Phone} label="Téléphone 1" value={proprietaire?.tel1} />
                            <InfoRow icon={Phone} label="Téléphone 2" value={proprietaire?.tel2} />
                            <InfoRow icon={Mail} label="Email" value={proprietaire?.email} />
                            <InfoRow icon={MapPin} label="Adresse" value={proprietaire?.adresse} />
                            <InfoRow icon={FileText} label="Profession" value={proprietaire?.profession} />
                            <InfoRow icon={FileText} label="Nationalité" value={proprietaire?.nationalite} />
                            <InfoRow icon={CalendarClock} label="Date de naissance" value={formatDate(proprietaire?.date_naiss)} />
                            <InfoRow icon={MapPin} label="Lieu de naissance" value={proprietaire?.lieu_naiss} />
                            <InfoRow icon={IdCard} label="N° pièce" value={proprietaire?.numpiece} />
                            <InfoRow icon={IdCard} label="Type de pièce" value={selectedTypePieceLabel} />
                            <InfoRow icon={CalendarClock} label="Expiration pièce" value={formatDate(proprietaire?.date_expiration_piece)} />
                        </div>

                        <Separator className="my-4" />

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Région</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{proprietaire?.region?.name || 'Non renseigné'}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Ville</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{proprietaire?.ville?.name || 'Non renseigné'}</p>
                            </div>
                        </div>
                    </Section>
                    </TabsContent>

                    <TabsContent value="representant" className="mt-0">
                    <Section id="liaison" icon={FileText} title="Représentant du propriétaire">
                        {liaison ? (
                            <div className="space-y-4">
                                <div className="grid grid-cols-1 gap-x-6 md:grid-cols-2">
                                    <InfoRow icon={CheckCircle2} label="Statut" value={liaison.is_active ? 'Actif' : 'Inactif'} />
                                    <InfoRow icon={CalendarClock} label="Activation" value={formatDate(liaison.date_activation)} />
                                    <InfoRow icon={CalendarClock} label="Désactivation" value={formatDate(liaison.date_desactivation)} />
                                    <InfoRow icon={UserRound} label="Représentant" value={liaison.name_representant} />
                                    <InfoRow icon={UserRound} label="Genre représentant" value={representativeGenreLabel} />
                                    <InfoRow icon={Phone} label="Téléphone 1" value={liaison.tel1_representant} />
                                    <InfoRow icon={Phone} label="Téléphone 2" value={liaison.tel2_representant} />
                                    <InfoRow icon={Mail} label="Email" value={liaison.email_representant} />
                                    <InfoRow icon={MapPin} label="Adresse représentant" value={liaison.adresse_representant} />
                                    <InfoRow icon={IdCard} label="Type de pièce" value={representativeTypePieceLabel} />
                                    <InfoRow icon={IdCard} label="N° pièce" value={liaison.numpiece_representant} />
                                </div>

                               <div className="rounded-xl  p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">
                                        Photo du représentant
                                    </p>

                                    {liaison.photo_representant ? (
                                        <img
                                            src={
                                                String(liaison.photo_representant).startsWith('http')
                                                    ? liaison.photo_representant
                                                    : `/storage/${liaison.photo_representant}`
                                            }
                                            alt="Photo du représentant"
                                            className="mt-3 h-40 w-40 rounded-xl border border-[#c8d4de] object-cover"
                                        />
                                    ) : (
                                        <p className="mt-2 text-sm text-[#5f7182]">
                                            Aucune photo renseignée.
                                        </p>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <EmptyState>Aucun représentant disponible.</EmptyState>
                        )}
                    </Section>
                    </TabsContent>

                    <TabsContent value="lots" className="mt-0">
                    <Section
                        id="lots"
                        icon={Layers}
                        title="Lots rattachés"
                        description={`${number(lotsList.length)} lot(s) enregistré(s).`}
                        action={
                            <Button type="button" size="sm" className={agenceButtonStyles.primary} onClick={openLotModal} title="Ajouter un lot">
                                <Plus className="h-4 w-4" />
                                Ajouter un lot
                            </Button>
                        }
                    >
                        {lotsList.length ? (
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {lotsList.map((lot) => (
                                    <LotCard
                                        key={lot.propreietaire_lot_id || lot.id}
                                        lot={lot}
                                        onEdit={() => openEditLotModal(lot)}
                                        onDelete={() => handleDeleteLot(lot)}
                                        canDelete={Number(lot.proprietes_count ?? 0) === 0}
                                    />
                                ))}
                            </div>
                        ) : (
                            <EmptyState>Aucun lot enregistré pour ce propriétaire.</EmptyState>
                        )}
                    </Section>
                    </TabsContent>

                    <TabsContent value="proprietes" className="mt-0">
                    <Section
                        id="proprietes"
                        icon={Building2}
                        title="Propriétés"
                        description="Biens liés à ce propriétaire dans l'agence."
                        action={
                            <Button asChild size="sm" className={agenceButtonStyles.primary} title="Ajouter une propriété">
                                <Link href={`/agence/proprietes/create?proprietaire_id=${encodeURIComponent(proprietaire?.proprietaire_id ?? '')}`}>
                                    <Plus className="h-4 w-4" />
                                    Ajouter
                                </Link>
                            </Button>
                        }
                    >
                        {proprietesList.length ? (
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {proprietesList.map((propriete) => (
                                    <PropertyCard key={propriete.propriete_id || propriete.id} propriete={propriete} />
                                ))}
                            </div>
                        ) : (
                            <EmptyState>Aucune propriété associée à ce propriétaire.</EmptyState>
                        )}
                    </Section>
                    </TabsContent>

                    <TabsContent value="documents" className="mt-0">
                        <Section
                            id="documents"
                            icon={FileText}
                            title="Documents du contrat propriétaire–agence"
                            description="Les documents sont générés avec les informations actuelles de l’agence et du propriétaire."
                        >
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="rounded-xl border border-[#cfe2f3] bg-[#f8fbfe] p-5">
                                    <div className="flex items-start gap-3">
                                        <div className="rounded-xl bg-[#eaf4fb] p-3 text-[#00559b]">
                                            <FileText className="h-6 w-6" />
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h3 className="font-semibold text-[#0f172a]">Mandat de gestion immobilière</h3>
                                            <p className="mt-1 text-sm text-[#5f7182]">
                                                Mandat complet précisant les biens confiés, les obligations et la rémunération de l’agence.
                                            </p>
                                        </div>
                                    </div>
                                    <Button asChild type="button" className={cn('mt-5 w-full', agenceButtonStyles.primary)}>
                                        <a href={`/agence/proprietaire/${proprietaire?.proprietaire_id}/documents/mandat`}>
                                            <Download className="h-4 w-4" />
                                            Télécharger le mandat
                                        </a>
                                    </Button>
                                </div>

                                <div className="rounded-xl border border-[#d9e7dc] bg-[#f8fcf9] p-5">
                                    <div className="flex items-start gap-3">
                                        <div className="rounded-xl bg-[#eaf7ed] p-3 text-[#24733f]">
                                            <FileText className="h-6 w-6" />
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h3 className="font-semibold text-[#0f172a]">Procuration</h3>
                                            <p className="mt-1 text-sm text-[#5f7182]">
                                                Autorisation donnée à l’agence pour représenter le propriétaire dans la gestion de ses biens.
                                            </p>
                                        </div>
                                    </div>
                                    <Button asChild type="button" className={cn('mt-5 w-full', agenceButtonStyles.primary)}>
                                        <a href={`/agence/proprietaire/${proprietaire?.proprietaire_id}/documents/procuration`}>
                                            <Download className="h-4 w-4" />
                                            Télécharger la procuration
                                        </a>
                                    </Button>
                                </div>
                            </div>

                            <div className="mt-4 rounded-xl border border-[#f0dfb5] bg-[#fffaf0] px-4 py-3 text-sm text-[#7a5b16]">
                                Les champs non complétés dans le dossier ou les paramètres de l’agence apparaîtront comme « Non renseigné » dans le PDF.
                            </div>
                        </Section>
                    </TabsContent>

                    <TabsContent value="paiements" className="mt-0">
                        <Section
                            id="paiements"
                            icon={Wallet}
                            title="Tableau récapitulatif consolidé des loyers payés"
                            description="Tous les encaissements de loyers rattachés aux biens de ce propriétaire."
                        >
                            <div className="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div className="rounded-xl border border-[#cfe2f3] bg-[#eaf4fb] p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#5f7182]">Total des loyers payés</p>
                                    <p className="mt-1 text-xl font-bold text-[#00559b]">{number(totalLoyersPayes)} FCFA</p>
                                </div>
                                <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#5f7182]">Locataires concernés</p>
                                    <p className="mt-1 text-xl font-bold text-[#0f172a]">{number(paymentRows.length)}</p>
                                </div>
                            </div>

                            {paymentRows.length ? (
                                <div className="overflow-x-auto rounded-xl border border-[#e2e8f0]">
                                    <table className="w-full min-w-[1250px] border-collapse text-sm">
                                        <thead>
                                            <tr>
                                                <th className="sticky left-0 z-20 min-w-[220px] border-b border-r border-[#d8d4eb] bg-[#e8e2f3] px-4 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#3f3a52]">
                                                    Locataire / loyer net
                                                </th>
                                                {paymentMonths.map((month, index) => (
                                                    <th
                                                        key={month.key}
                                                        className={cn(
                                                            'min-w-[100px] border-b border-r border-[#e2e8f0] px-3 py-4 text-center text-xs font-semibold text-[#475569]',
                                                            index === 0 ? 'bg-[#f6edc9]' : 'bg-[#f8fafc]'
                                                        )}
                                                    >
                                                        {month.label}
                                                    </th>
                                                ))}
                                                <th className="min-w-[125px] border-b border-[#cfe2f3] bg-[#dcecf8] px-4 py-4 text-right text-xs font-semibold uppercase text-[#00559b]">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {paymentRows.map((row) => (
                                                <tr key={row.id} className="odd:bg-white even:bg-[#fbfcfd]">
                                                    <th className="sticky left-0 z-10 border-b border-r border-[#d8d4eb] bg-[#e8e2f3] px-4 py-4 text-left text-xs font-semibold uppercase text-[#3f3a52]">
                                                        <span className="block">{row.name}</span>
                                                        <span className="mt-1 block text-[11px] font-medium normal-case text-[#655f78]">
                                                            Loyer net : {number(row.loyerNet)} FCFA
                                                        </span>
                                                    </th>
                                                    {paymentMonths.map((month, index) => (
                                                        <td
                                                            key={`${row.id}-${month.key}`}
                                                            className={cn(
                                                                'border-b border-r border-[#e2e8f0] px-3 py-4 text-right tabular-nums text-[#334155]',
                                                                index === 0 ? 'bg-[#fbf4dc]' : ''
                                                            )}
                                                        >
                                                            {number(row.months[month.key] ?? 0)}
                                                        </td>
                                                    ))}
                                                    <td className="border-b border-[#cfe2f3] bg-[#dcecf8] px-4 py-4 text-right font-semibold tabular-nums text-[#00559b]">
                                                        {number(row.total)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th className="sticky left-0 z-20 border-r border-[#bfd8c4] bg-[#dcecf8] px-4 py-4 text-left text-xs font-bold uppercase text-[#0f172a]">Totaux</th>
                                                {paymentMonths.map((month) => (
                                                    <td key={`total-${month.key}`} className="border-r border-[#bfd8c4] bg-[#dcebd9] px-3 py-4 text-right font-bold tabular-nums text-[#244b2b]">
                                                        {number(monthTotals[month.key] ?? 0)}
                                                    </td>
                                                ))}
                                                <td className="bg-[#a9d2bd] px-4 py-4 text-right font-bold tabular-nums text-[#163b29]">{number(totalLoyersPayes)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            ) : (
                                <EmptyState>Aucun paiement de loyer enregistré pour ce propriétaire.</EmptyState>
                            )}
                        </Section>
                    </TabsContent>

                    <TabsContent value="consolide" className="mt-0 space-y-4">
                        <Section
                            id="consolide"
                            icon={Wallet}
                            title="Consolidé des loyers payés"
                            description="Résumé des reversements effectués et restant à effectuer, regroupé par lot."
                        >
                            <div className="mb-5 grid grid-cols-1 gap-4 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                                <Field label="Date de début">
                                    <Input type="date" value={consolidationStart} onChange={(event) => setConsolidationStart(event.target.value)} />
                                </Field>
                                <Field label="Date de fin">
                                    <Input type="date" value={consolidationEnd} onChange={(event) => setConsolidationEnd(event.target.value)} />
                                </Field>
                                <Button type="button" className={agenceButtonStyles.primary} onClick={() => setActiveTab('consolide')}>
                                    Consolider
                                </Button>
                            </div>

                            <div className="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <div className="rounded-xl border border-[#cfe2f3] bg-[#eaf4fb] p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#5f7182]">Total loyers</p>
                                    <p className="mt-1 text-xl font-bold text-[#00559b]">{number(consolidatedTotals.totalLoyer)} FCFA</p>
                                </div>
                                <div className="rounded-xl border border-[#d8ebb7] bg-[#f2f9e8] p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#5f7182]">Commission</p>
                                    <p className="mt-1 text-xl font-bold text-[#4d8500]">{number(consolidatedTotals.commission)} FCFA</p>
                                </div>
                                <div className="rounded-xl border border-[#fde7bf] bg-[#fff8e8] p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#5f7182]">Dépenses</p>
                                    <p className="mt-1 text-xl font-bold text-[#b45309]">{number(consolidatedTotals.depenses)} FCFA</p>
                                </div>
                                <div className="rounded-xl border border-[#f4c9c4] bg-[#fdecea] p-4">
                                    <p className="text-xs uppercase tracking-wide text-[#5f7182]">Net à reverser</p>
                                    <p className="mt-1 text-xl font-bold text-[#b42318]">
                                        {number(consolidatedTotals.montantReverse)} FCFA
                                    </p>
                                </div>
                            </div>

                            {consolidatedProperties.length ? (
                                <div className="overflow-x-auto rounded-xl border border-[#e2e8f0]">
                                    <table className="w-full min-w-[1200px] text-sm">
                                        <thead className="bg-[#f1f5f9] text-xs uppercase tracking-wide text-[#475569]">
                                            <tr>
                                                <th className="px-4 py-3 text-left">Lot</th>
                                                <th className="px-4 py-3 text-right">Total loyer</th>
                                                <th className="px-4 py-3 text-right">Commission</th>
                                                <th className="px-4 py-3 text-right">Après commission</th>
                                                <th className="px-4 py-3 text-right">Nouvelle caution</th>
                                                <th className="px-4 py-3 text-right">Dépenses</th>
                                                <th className="bg-[#e8f4eb] px-4 py-3 text-right text-[#4d8500]">Net à reverser</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-[#e2e8f0] bg-white">
                                            {consolidatedProperties.map((row) => {
                                                return (
                                                    <tr key={row.id}>
                                                        <td className="px-4 py-4 text-left">
                                                            <p className="font-semibold text-[#0f172a]">{row.reference}</p>
                                                            <p className="text-xs text-[#5f7182]">{row.adresse || 'Adresse non renseignée'}</p>
                                                        </td>
                                                        <td className="px-4 py-4 text-right tabular-nums">{number(row.totalLoyer)} FCFA</td>
                                                        <td className="px-4 py-4 text-right tabular-nums">{number(row.commission)} FCFA</td>
                                                        <td className="px-4 py-4 text-right tabular-nums">{number(row.apresCommission)} FCFA</td>
                                                        <td className="px-4 py-4 text-right tabular-nums">{number(row.nouvelleCaution)} FCFA</td>
                                                        <td className="px-4 py-4 text-right tabular-nums text-[#b45309]">{number(row.depenses)} FCFA</td>
                                                        <td className="bg-[#f2f9e8] px-4 py-4 text-right font-bold tabular-nums text-[#4d8500]">{number(row.montantReverse)} FCFA</td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                        <tfoot>
                                            <tr className="bg-[#e2eadf] font-bold text-[#0f172a]">
                                                <th className="px-4 py-4 text-left">Totaux</th>
                                                <td className="px-4 py-4 text-right">{number(consolidatedTotals.totalLoyer)} FCFA</td>
                                                <td className="px-4 py-4 text-right">{number(consolidatedTotals.commission)} FCFA</td>
                                                <td className="px-4 py-4 text-right">{number(consolidatedTotals.apresCommission)} FCFA</td>
                                                <td className="px-4 py-4 text-right">{number(consolidatedTotals.nouvelleCaution)} FCFA</td>
                                                <td className="px-4 py-4 text-right">{number(consolidatedTotals.depenses)} FCFA</td>
                                                <td className="bg-[#a9d2bd] px-4 py-4 text-right text-[#163b29]">
                                                    {number(consolidatedTotals.montantReverse)} FCFA
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            ) : (
                                <EmptyState>Aucun reversement trouvé pour cette période.</EmptyState>
                            )}
                        </Section>
                    </TabsContent>
                </Tabs>
            </div>

            {isLotModalOpen ? (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
                    <div className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-[#c8d4de] bg-white shadow-2xl">
                        <div className="flex items-start justify-between gap-4 border-b border-[#e2e8f0] px-6 py-5">
                            <div>
                               
                                <h3 className="text-lg font-semibold text-[#0f172a]">
                                    {editingLot ? 'Modifier le lot' : `Ajouter un lot pour ${proprietaire?.name || 'ce propriétaire'}`}
                                </h3>
                            </div>
                            <Button type="button" variant="outline" size="icon" className={agenceButtonStyles.outline} onClick={closeLotModal} title="Fermer la fenêtre">
                                <X className="h-4 w-4" />
                            </Button>
                        </div>

                        <form onSubmit={handleLotSubmit} className="px-6 py-6">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Field label="Nom du lot" required >
                                    <Input
                                        type="text"
                                        value={lotForm.name}
                                        onChange={(event) => setLotForm((current) => ({ ...current, name: event.target.value }))}
                                        placeholder="Lot principal"
                                    />
                                </Field>

                                <Field label="Superficie (m²)">
                                    <Input
                                        type="number"
                                        min="0"
                                        step="1"
                                        value={lotForm.superficie}
                                        onChange={(event) => setLotForm((current) => ({ ...current, superficie: event.target.value }))}
                                        placeholder="150"
                                    />
                                </Field>

                                <Field label="N° lot">
                                    <Input
                                        type="text"
                                        value={lotForm.num_lot}
                                        onChange={(event) => setLotForm((current) => ({ ...current, num_lot: event.target.value }))}
                                        placeholder="001"
                                    />
                                </Field>

                                <Field label="N° îlot">
                                    <Input
                                        type="text"
                                        value={lotForm.num_ilot}
                                        onChange={(event) => setLotForm((current) => ({ ...current, num_ilot: event.target.value }))}
                                        placeholder="A"
                                    />
                                </Field>

                                

                                <Field label="Adresse" className="md:col-span-2">
                                    <Input
                                        type="text"
                                        value={lotForm.adresse}
                                        onChange={(event) => setLotForm((current) => ({ ...current, adresse: event.target.value }))}
                                        placeholder="Cocody Riviera 3"
                                    />
                                </Field>

                                <Field label="Région">
                                    <Select
                                        value={lotForm.region_id}
                                        onValueChange={(value) =>
                                            setLotForm((current) => ({ ...current, region_id: value, ville_id: '' }))
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Aucune</SelectItem>
                                            {regionOptions.map((region) => (
                                                <SelectItem key={region.value} value={region.value}>
                                                    {region.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Ville">
                                    <Select
                                        value={lotForm.ville_id}
                                        onValueChange={(value) => setLotForm((current) => ({ ...current, ville_id: value }))}
                                        disabled={!lotForm.region_id}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder={lotForm.region_id ? 'Sélectionner' : "Sélectionnez d'abord une région"} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Aucune</SelectItem>
                                            {villeOptions.map((ville) => (
                                                <SelectItem key={ville.value} value={ville.value}>
                                                    {ville.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>
                                <label className="flex items-center gap-3 rounded-xl border border-[#c8d4de] p-3 md:col-span-2">
                                    <input
                                        type="checkbox"
                                        checked={lotForm.is_for_sale}
                                        onChange={(event) => setLotForm((current) => ({ ...current, is_for_sale: event.target.checked, sale_price: event.target.checked ? current.sale_price : '' }))}
                                        className="h-4 w-4 accent-[#00559b]"
                                    />
                                    <span className="text-sm font-medium text-[#0f172a]">Mettre le lot entier en vente</span>
                                </label>
                                {lotForm.is_for_sale ? (
                                    <Field label="Prix de vente du lot" required className="md:col-span-2">
                                        <Input type="number" min="1" value={lotForm.sale_price} onChange={(event) => setLotForm((current) => ({ ...current, sale_price: event.target.value }))} placeholder="Ex : 50000000" />
                                    </Field>
                                ) : null}
                            </div>

                            <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <Button type="button" variant="outline" className={agenceButtonStyles.outline} onClick={closeLotModal} disabled={isSavingLot} title="Annuler les modifications">
                                    Annuler
                                </Button>
                                <Button type="submit" className={agenceButtonStyles.primary} disabled={isSavingLot} title={editingLot ? 'Mettre à jour le lot' : 'Enregistrer le lot'}>
                                    {isSavingLot ? 'Enregistrement...' : editingLot ? 'Mettre a jour' : 'Enregistrer'}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            ) : null}
        </AgenceLayout>
    );
}
