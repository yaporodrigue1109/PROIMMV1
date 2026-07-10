import { useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    CalendarClock,
    CheckCircle2,
    DoorOpen,
    Home,
    Layers3,
    MapPin,
    Pencil,
    Phone,
    Ruler,
    Tag,
    Wallet,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

// ─────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────

const COLORS = {
    blue: '#00559b',
    green: '#76c206',
    greenDark: '#4d8500',
    slate: '#5f7182',
    border: '#c8d4de',
    ink: '#0f172a',
    amber: '#b45309',
};

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const money = (value) => `${number(value)} FCFA`;

const wholeNumber = (value) =>
    new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const formatDate = (value) => {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }).format(date);
};

function initials(name) {
    return String(name ?? '')
        .split(' ')
        .filter(Boolean)
        .map((word) => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function normalizePorte(porte, equipementLabelMap = new Map()) {
    const tarif = porte?.tarif ?? {};

    return {
        id: porte?.porte_id ?? porte?.id ?? crypto.randomUUID(),
        numero_porte: porte?.numero_porte ?? 'N/A',
        type: porte?.type_porte?.name ?? porte?.type?.name ?? porte?.type?.libelle ?? porte?.type_porte ?? 'N/A',
        superficie_m2: porte?.superficie_m2 ?? null,
        etage: Number(porte?.etage ?? 0),
        is_allocation: Boolean(porte?.is_allocation ?? true),
        description: porte?.description ?? '',
        is_occupe: Boolean(porte?.is_occupe),
        is_actif: porte?.is_actif ?? true,
        equipements: (Array.isArray(porte?.equipements) ? porte.equipements : []).map((item) => {
            if (typeof item === 'string' || typeof item === 'number') {
                const key = String(item);
                return equipementLabelMap.get(key) || key;
            }

            return item?.name ?? item?.label ?? '';
        }),
        tarif: {
            mt_loyer: tarif.mt_loyer ?? 0,
            mt_vente: tarif.mt_vente ?? 0,
            mt_caution: tarif.mt_caution ?? 0,
            mt_avance: tarif.mt_avance ?? 0,
            mt_frais_agence: tarif.mt_frais_agence ?? 0,
            mt_frais_dossier: tarif.mt_frais_dossier ?? null,
            mt_caution_cie: tarif.mt_caution_cie ?? 0,
            mt_caution_sodeci: tarif.mt_caution_sodeci ?? 0,
            date_effet: tarif.date_effet ?? null,
        },
    };
}

function normalizeBatiment(batiment, equipementLabelMap = new Map()) {
    const portes = (Array.isArray(batiment?.portes) ? batiment.portes : []).map((porte) =>
        normalizePorte(porte, equipementLabelMap)
    );

    return {
        id: batiment?.batiment_id ?? batiment?.id ?? crypto.randomUUID(),
        name: batiment?.name ?? 'Bâtiment',
        description: batiment?.description ?? '',
        nbre_etages: Number(batiment?.nbre_etages ?? 0),
        portes,
        portes_total: portes.length,
        portes_occupees: portes.filter((porte) => porte.is_occupe).length,
    };
}

function normalizeProperty(propriete, equipementLabelMap = new Map()) {
    const batiments = (Array.isArray(propriete?.batiments) ? propriete.batiments : []).map((batiment) =>
        normalizeBatiment(batiment, equipementLabelMap)
    );
    const portes_total = batiments.reduce((acc, b) => acc + b.portes_total, 0);
    const portes_occupees = batiments.reduce((acc, b) => acc + b.portes_occupees, 0);
    const porteModes = batiments.flatMap((b) => b.portes.map((porte) => porte.is_allocation));
    let market_mode = propriete?.is_allocation ? 'location' : 'vente';

    if (porteModes.length) {
        const hasLocation = porteModes.some((value) => value === true);
        const hasSale = porteModes.some((value) => value === false);
        market_mode = hasLocation && hasSale ? 'mixte' : hasLocation ? 'location' : 'vente';
    }

    return {
        id: propriete?.id ?? propriete?.propriete_id ?? '',
        reference: propriete?.reference ?? 'N/A',
        description: propriete?.description ?? '',
        adresse_complete: propriete?.adresse_complete ?? propriete?.lot?.adresse ?? '',
        is_allocation: Boolean(propriete?.is_allocation ?? true),
        is_actif: propriete?.is_actif ?? true,
        type: propriete?.type?.name ?? propriete?.type ?? 'N/A',
        lot: propriete?.lot?.name ?? propriete?.lot ?? '',
        market_mode,
        proprietaire: {
            name: propriete?.proprietaire?.name ?? 'N/A',
            tel1: propriete?.proprietaire?.tel1 ?? '',
            email: propriete?.proprietaire?.email ?? '',
        },
        proximites: (Array.isArray(propriete?.proximites) ? propriete.proximites : []).map((item) => {
            if (typeof item === 'string' || typeof item === 'number') {
                return {
                    id: String(item),
                    name: String(item),
                    description: '',
                    distance: '',
                    unite: 'm',
                };
            }

            return {
                id: String(item?.id ?? item?.proximite_id ?? ''),
                name: item?.name ?? '',
                description: item?.description ?? '',
                distance: item?.distance ?? '',
                unite: item?.unite ?? 'm',
            };
        }),
        batiments,
        batiments_count: batiments.length,
        portes_total,
        portes_occupees,
        portes_libres: Math.max(portes_total - portes_occupees, 0),
        progress: portes_total ? Math.round((portes_occupees / portes_total) * 100) : 0,
    };
}

function normalizeProximite(proximite, referentielMap) {
    if (proximite && typeof proximite === 'object') {
        return {
            id: proximite.id ?? proximite.proximite_id ?? proximite.value ?? crypto.randomUUID(),
            name: proximite.name ?? proximite.label ?? String(proximite.id ?? proximite.value ?? 'N/A'),
            description: proximite.description ?? '',
            distance: proximite.distance ?? '',
            unite: proximite.unite ?? 'm',
        };
    }

    const id = String(proximite ?? '');

    return {
        id,
        name: referentielMap.get(id) ?? id,
        description: '',
        distance: '',
        unite: 'm',
    };
}

// ─────────────────────────────────────────────────────────────
// UI blocks
// ─────────────────────────────────────────────────────────────

function StatCard({ icon: Icon, label, value, accent = COLORS.blue, tint = '#eaf4fb' }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardContent className="flex items-center gap-3 p-4 mt-6">
                <span
                    className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                    style={{ backgroundColor: tint, color: accent }}
                >
                    <Icon className="h-5 w-5" />
                </span>
                <div className="min-w-0">
                    <p className="text-xl font-bold text-[#0f172a]">{value}</p>
                    <p className="truncate text-[11px] uppercase tracking-wide text-[#94a3b8]">{label}</p>
                </div>
            </CardContent>
        </Card>
    );
}

function InfoRow({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-3 py-2.5">
            <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1f5f9] text-[#5f7182]">
                <Icon className="h-4 w-4" />
            </span>
            <div className="min-w-0">
                <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">{label}</p>
                <p className="break-words text-sm font-medium text-[#0f172a]">{value || '—'}</p>
            </div>
        </div>
    );
}

function TarifLine({ label, value, strong = false }) {
    return (
        <div className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3">
            <span className="min-w-0 text-sm text-[#5f7182]">{label}</span>
            <span
                className={cn(
                    'whitespace-nowrap text-right text-sm text-[#0f172a]',
                    strong ? 'font-bold' : 'font-medium'
                )}
            >
                {value}
            </span>
        </div>
    );
}

function formatBatimentEtageLabel(nbreEtages) {
    const total = Number(nbreEtages ?? 0);

    if (total <= 0) {
        return 'Sans étage';
    }

    return `${total} étage${total > 1 ? 's' : ''}`;
}

function formatPorteEtageLabel(etage, totalEtages) {
    const floor = Number(etage ?? 0);
    const buildingFloors = Number(totalEtages ?? 0);

    if (buildingFloors <= 0) {
        return '';
    }

    return floor === 0 ? ' · 0 - RDC' : ` · Étage ${floor}`;
}

function allocationPorteBadge(isAllocation) {
    return isAllocation ? (
        <Badge variant="warning" className="rounded-full text-xs ring-1 ring-[#bfdff2]">
            Location
        </Badge>
    ) : (
        <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
            Vente
        </Badge>
    );
}

function marketModeBadge(mode) {
    if (mode === 'mixte') {
        return (
            <Badge variant="warning" className="rounded-full text-xs ring-1 ring-[#fcd34d]">
                Mixte
            </Badge>
        );
    }

    return mode === 'vente' ? (
        <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
            Vente
        </Badge>
    ) : (
        <Badge variant="warning" className="rounded-full text-xs ring-1 ring-[#bfdff2]">
            Location
        </Badge>
    );
}

function PorteCard({ porte, index, batimentEtages = 0 }) {
    return (
        <div className="rounded-2xl border border-[#c8d4de] bg-[#f8fafc] p-4">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex items-center gap-2.5">
                    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                        <DoorOpen className="h-4 w-4" />
                    </span>
                    <div className="min-w-0">
                        <p className="text-sm font-semibold text-[#0f172a]">
                            Porte {porte.numero_porte || `#${index + 1}`}
                        </p>
                        <p className="text-xs text-[#5f7182]">
                            {porte.type}
                            {formatPorteEtageLabel(porte.etage, batimentEtages)}
                            {porte.superficie_m2 ? ` · ${porte.superficie_m2} m²` : ''}
                        </p>
                    </div>
                </div>
                <div className="flex flex-wrap justify-end gap-1.5">
                    {allocationPorteBadge(porte.is_allocation)}
                    {porte.is_occupe ? (
                        <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
                            Occupée
                        </Badge>
                    ) : (
                        <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
                            Libre
                        </Badge>
                    )}
                    {!porte.is_actif ? (
                        <Badge variant="outline" className="rounded-full text-xs ring-1 ring-[#c8d4de]">
                            Inactive
                        </Badge>
                    ) : null}
                </div>
            </div>

            {porte.description ? (
                <p className="mt-3 text-sm text-[#5f7182]">{porte.description}</p>
            ) : null}

            {porte.equipements.length ? (
                <div className="mt-3 flex flex-wrap gap-1.5">
                    {porte.equipements.map((equip, i) => (
                        <span
                            key={`${porte.id}-equip-${i}`}
                            className="rounded-full border border-[#c8d4de] bg-white px-2.5 py-0.5 text-xs text-[#5f7182]"
                        >
                            {equip}
                        </span>
                    ))}
                </div>
            ) : null}

            <Separator className="my-4" />

            <div className="rounded-xl border border-[#e2e8f0] bg-white p-4">
                <div className="mb-4 flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                    <Wallet className="h-4 w-4 text-[#00559b]" />
                    Tarification
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-x-8 sm:gap-y-4">
                    <div className="space-y-3">
                        <TarifLine
                            label={porte.is_allocation ? 'Loyer' : 'Prix de vente'}
                            value={money(porte.is_allocation ? porte.tarif.mt_loyer : porte.tarif.mt_vente)}
                            strong
                        />
                        {porte.is_allocation ? (
                            <>
                                <TarifLine label="Avance (mois)" value={wholeNumber(porte.tarif.mt_avance)} />
                                <TarifLine label="Caution CIE (montant)" value={money(porte.tarif.mt_caution_cie)} />
                            </>
                        ) : null}
                    </div>

                    <div className="space-y-3">
                        {porte.is_allocation ? (
                            <>
                                <TarifLine label="Caution (mois)" value={wholeNumber(porte.tarif.mt_caution)} />
                                <TarifLine label="Frais d'agence (mois)" value={wholeNumber(porte.tarif.mt_frais_agence)} />
                                <TarifLine label="Caution SODECI (montant)" value={money(porte.tarif.mt_caution_sodeci)} />
                            </>
                        ) : null}
                    </div>
                </div>

                <Separator className="my-4" />

                <TarifLine
                    label="Frais de dossier"
                    value={porte.tarif.mt_frais_dossier === null || porte.tarif.mt_frais_dossier === '' ? '—' : money(porte.tarif.mt_frais_dossier)}
                />
            </div>
        </div>
    );
}

function BatimentPanel({ batiment }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eef8df] text-[#4d8500]">
                        <Building2 className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">{batiment.name}</CardTitle>
                        <CardDescription className="text-xs text-[#5f7182]">
                            {formatBatimentEtageLabel(batiment.nbre_etages)} ·{' '}
                            {batiment.portes_total} porte{batiment.portes_total > 1 ? 's' : ''} ·{' '}
                            {batiment.portes_occupees} occupée{batiment.portes_occupees > 1 ? 's' : ''}
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="mt-6 space-y-4 p-5">
                {batiment.description ? (
                    <p className="text-sm text-[#5f7182]">{batiment.description}</p>
                ) : null}

                {batiment.portes.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                        <p className="text-sm font-semibold text-[#0f172a]">Aucune porte</p>
                        <p className="text-sm text-[#5f7182]">Ce bâtiment ne contient pas encore de porte.</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-4">
                        {batiment.portes.map((porte, index) => (
                            <PorteCard key={porte.id} porte={porte} index={index} batimentEtages={batiment.nbre_etages} />
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

// ─────────────────────────────────────────────────────────────
// Page
// ─────────────────────────────────────────────────────────────

export default function Show({ propriete = null, proximites = [], equipements = [] }) {
    const proximityLabelMap = useMemo(
        () =>
            new Map(
                (Array.isArray(proximites) ? proximites : []).map((item) => [
                    String(item?.id ?? item?.proximite_id ?? ''),
                    item?.name ?? item?.label ?? '',
                ])
            ),
        [proximites]
    );
    const equipementLabelMap = useMemo(
        () =>
            new Map(
                (Array.isArray(equipements) ? equipements : []).map((item) => [
                    String(item?.id ?? item?.equipement_id ?? ''),
                    item?.name ?? item?.label ?? '',
                ])
            ),
        [equipements]
    );
    const property = useMemo(() => {
        const normalized = normalizeProperty(propriete ?? {}, equipementLabelMap);

        return {
            ...normalized,
            proximites: normalized.proximites.map((item) => normalizeProximite(item, proximityLabelMap)),
        };
    }, [propriete, proximityLabelMap, equipementLabelMap]);
    const [activeBatiment, setActiveBatiment] = useState(
        property.batiments[0]?.id ? String(property.batiments[0].id) : ''
    );

    return (
        <AgenceLayout title={`Propriété ${property.reference}`}>
            <Head title={`Propriété ${property.reference}`} />

            <div className="mx-auto flex max-w-6xl flex-col gap-6 pb-10">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-3">
                        <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                            <Link href="/agence/proprietes">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="truncate text-xl font-semibold text-[#0f172a]">
                                    {property.adresse_complete || 'Propriété'}
                                </h1>
                                {marketModeBadge(property.market_mode)}
                                {property.is_actif ? (
                                    <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
                                        Active
                                    </Badge>
                                ) : (
                                    <Badge variant="outline" className="rounded-full text-xs ring-1 ring-[#c8d4de]">
                                        Inactive
                                    </Badge>
                                )}
                            </div>
                            <p className="font-mono text-xs text-[#5f7182]">{property.reference}</p>
                        </div>
                    </div>

                    <Button asChild className={agenceButtonStyles.primary}>
                        <Link href={`/agence/proprietes/edit/${property.id}`}>
                            <Pencil className="mr-2 h-4 w-4" /> Modifier
                        </Link>
                    </Button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <StatCard icon={Building2} label="Bâtiments" value={number(property.batiments_count)} />
                    <StatCard icon={DoorOpen} label="Portes totales" value={number(property.portes_total)} />
                    <StatCard
                        icon={CheckCircle2}
                        label="Portes libres"
                        value={number(property.portes_libres)}
                        accent={COLORS.greenDark}
                        tint="#eef8df"
                    />
                    <StatCard
                        icon={Layers3}
                        label="Taux d'occupation"
                        value={`${property.progress}%`}
                        accent={COLORS.amber}
                        tint="#fef3c7"
                    />
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Détails */}
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm lg:col-span-1">
                        <CardHeader className="border-b border-[#e2e8f0] py-4">
                            <CardTitle className="text-sm text-[#0f172a]">Informations</CardTitle>
                        </CardHeader>
                        <CardContent className="mt-6 p-5">
                            <div className="flex items-center gap-3 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-3">
                                <Avatar className="h-10 w-10 border border-[#c8d4de] bg-[#eaf4fb]">
                                    <AvatarFallback className="bg-[#eaf4fb] text-xs font-bold text-[#00559b]">
                                        {initials(property.proprietaire.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-semibold text-[#0f172a]">
                                        {property.proprietaire.name}
                                    </p>
                                    <p className="truncate text-xs text-[#5f7182]">Propriétaire</p>
                                </div>
                            </div>

                            <Separator className="mt-6" />

                           
                            <InfoRow icon={MapPin} label="Adresse" value={property.adresse_complete} />
                            <InfoRow icon={Home} label="Lot" value={property.lot} />
                            {property.proprietaire.tel1 ? (
                                <InfoRow icon={Phone} label="Téléphone" value={property.proprietaire.tel1} />
                            ) : null}

                            {property.description ? (
                                <>
                                    <Separator className="mt-6" />
                                    <p className="mt-6 text-[11px] uppercase tracking-wide text-[#94a3b8]">Description</p>
                                    <p className="mt-1 text-sm text-[#5f7182]">{property.description}</p>
                                </>
                            ) : null}

                            {property.proximites.length ? (
                                <>
                                    <Separator className="mt-6" />
                                    <p className="mt-6 text-[11px] uppercase tracking-wide text-[#94a3b8]">Proximités</p>
                                    <div className="mt-2 space-y-2">
                                        {property.proximites.map((prox, i) => (
                                            <div
                                                key={`prox-${i}`}
                                                className="flex flex-col gap-1 rounded-xl border border-[#c8d4de] bg-white px-3 py-2"
                                            >
                                                <p className="text-xs font-semibold text-[#0f172a]">
                                                    <span className="mr-1 text-[#00559b]">~</span>
                                                    {prox.name}
                                                </p>
                                                <p className="text-xs text-[#5f7182]">
                                                    {prox.distance || '—'} {prox.unite || ''}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {/* Bâtiments & portes */}
                    <div className="lg:col-span-2">
                        {property.batiments.length === 0 ? (
                            <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                <CardContent className="flex flex-col items-center justify-center gap-2 px-6 py-16 text-center">
                                    <span className="flex h-12 w-12 items-center justify-center rounded-full bg-[#f1f5f9] text-[#94a3b8]">
                                        <Building2 className="h-6 w-6" />
                                    </span>
                                    <p className="text-sm font-semibold text-[#0f172a]">Aucun bâtiment</p>
                                    <p className="max-w-sm text-sm text-[#5f7182]">
                                        Cette propriété ne contient pas encore de bâtiment.
                                    </p>
                                </CardContent>
                            </Card>
                        ) : (
                            <Tabs value={activeBatiment} onValueChange={setActiveBatiment} className="w-full">
                                <TabsList className="mb-4 flex h-auto w-full flex-wrap justify-start gap-1 bg-[#eef2f6] p-1">
                                    {property.batiments.map((batiment) => (
                                        <TabsTrigger
                                            key={batiment.id}
                                            value={String(batiment.id)}
                                            className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]"
                                        >
                                            {batiment.name}
                                        </TabsTrigger>
                                    ))}
                                </TabsList>

                                {property.batiments.map((batiment) => (
                                    <TabsContent key={batiment.id} value={String(batiment.id)} className="mt-0">
                                        <BatimentPanel batiment={batiment} />
                                    </TabsContent>
                                ))}
                            </Tabs>
                        )}
                    </div>
                </div>
            </div>
        </AgenceLayout>
    );
}
