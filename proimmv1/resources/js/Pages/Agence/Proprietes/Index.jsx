import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Building2,
    CalendarClock,
    DoorOpen,
    Eye,
    Home,
    Inbox,
    MapPin,
    Pencil,
    Plus,
    Power,
    Settings2,
    Tag,
    TriangleAlert,
    X,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { DataTable } from '../../../components/ui/data-table';
import { DataTableColumnHeader } from '../../../components/ui/data-table-column-header';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '../../../components/ui/dialog';
import { Input } from '../../../components/ui/input';
import { ScrollArea } from '../../../components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { Separator } from '../../../components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

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

const DEMO_PROPERTIES = [
    {
        id: '1',
        reference: 'PROP-2026-0001',
        description: 'Exemple de propriété de démonstration',
        adresse_complete: 'Cocody, Abidjan',
        is_allocation: true,
        is_actif: true,
        type: { id: 1, name: 'Appartement' },
        proprietaire: { id: 1, name: 'SCI Lagune Bleue', tel1: '0700000000' },
        batiments_count: 2,
        portes_total: 24,
        portes_libres: 18,
        portes_occupees: 6,
        progress: 25,
    },
];

const DEMO_TYPES = [
    { id: 1, name: 'Appartement' },
    { id: 2, name: 'Villa' },
    { id: 3, name: 'Immeuble résidentiel' },
];

const DEMO_REFERENTIELS = {
    equipements: [{ id: 1, name: 'Piscine' }, { id: 2, name: 'Parking privé' }],
    proximites: [{ id: 1, name: 'École' }, { id: 2, name: 'Marché' }],
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

function normalizeProperty(property) {
    return {
        id: property?.id ?? property?.propriete_id ?? property?.reference ?? crypto.randomUUID(),
        reference: property?.reference ?? property?.ref ?? 'N/A',
        description: property?.description ?? property?.desc ?? '',
        adresse_complete: property?.adresse_complete ?? property?.addr ?? property?.address ?? '',
        is_allocation: Boolean(property?.is_allocation ?? property?.allocation ?? false),
        is_actif: Boolean(property?.is_actif ?? property?.active ?? true),
        type: property?.type
            ? {
                  id: property.type.id ?? property.type.type_propriete_id ?? '',
                  name: property.type.name ?? property.type.label ?? 'N/A',
              }
            : { id: '', name: property?.type ?? 'N/A' },
        proprietaire: property?.proprietaire
            ? {
                  id: property.proprietaire.id ?? '',
                  name: property.proprietaire.name ?? 'N/A',
                  tel1: property.proprietaire.tel1 ?? '',
              }
            : {
                  id: '',
                  name: property?.owner ?? 'N/A',
                  tel1: property?.ownerPhone ?? '',
              },
        batiments_count: Number(property?.batiments_count ?? property?.buildings ?? 0),
        portes_total: Number(property?.portes_total ?? property?.doorsTotal ?? 0),
        portes_libres: Number(property?.portes_libres ?? property?.doorsFree ?? 0),
        portes_occupees: Number(property?.portes_occupees ?? property?.doorsOccupied ?? 0),
        progress:
            Number(
                property?.progress ??
                    (property?.portes_total
                        ? Math.round((Number(property?.portes_occupees ?? 0) / Number(property?.portes_total ?? 1)) * 100)
                        : 0)
            ) || 0,
    };
}

function normalizeReferenceItem(item) {
    return {
        id: item?.id ?? crypto.randomUUID(),
        name: item?.name ?? item?.libelle ?? '',
        description: item?.description ?? '',
    };
}

function normalizeAllocationFilter(value) {
    if (value === true || value === 1 || value === '1' || value === 'true') {
        return 'allocation';
    }

    if (value === false || value === 0 || value === '0' || value === 'false') {
        return 'vente';
    }

    return 'all';
}

function allocationBadge(isAllocation) {
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

function activeBadge(isActive) {
    return isActive ? (
        <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
            Active
        </Badge>
    ) : (
        <Badge variant="outline" className="rounded-full text-xs ring-1 ring-[#c8d4de]">
            Inactive
        </Badge>
    );
}

function AvatarName({ name }) {
    return (
        <Avatar className="h-9 w-9 border border-[#c8d4de] bg-[#eaf4fb]">
            <AvatarFallback className="bg-[#eaf4fb] text-[11px] font-bold text-[#00559b]">
                {initials(name)}
            </AvatarFallback>
        </Avatar>
    );
}

function EmptyState({ title, desc, onReset, compact = false }) {
    return (
        <div className={cn('flex flex-col items-center justify-center gap-3 text-center', compact ? 'px-4 py-8' : 'px-6 py-14')}>
            <span className="flex h-13 w-13 items-center justify-center rounded-full bg-[#f1f5f9] p-3 text-[#94a3b8]">
                <Inbox className="h-6 w-6" />
            </span>
            <p className="text-sm font-semibold text-[#0f172a]">{title}</p>
            <p className="max-w-sm text-sm text-[#5f7182]">{desc}</p>
            {onReset ? (
                <Button variant="outline" size="sm" className={agenceButtonStyles.outline} onClick={onReset}>
                    Réinitialiser les filtres
                </Button>
            ) : null}
        </div>
    );
}

function PropertyCard({ property, onDisable }) {
    return (
        <Card className={cn('rounded-2xl border-[#c8d4de] bg-white shadow-sm', !property.is_actif && 'opacity-60')}>
            <CardContent className="flex flex-col gap-3 p-4">
                <div className="mt-3 flex items-start justify-between gap-3">
                    <div className="min-w-0">
                        <p className="font-mono text-xs text-[#5f7182]">{property.reference}</p>
                        <p className="truncate text-[15px] font-bold text-[#0f172a]">{property.adresse_complete}</p>
                        <p className="text-xs text-[#94a3b8]">{property.description || 'Aucune description'}</p>
                    </div>
                    <div className="flex flex-shrink-0 flex-wrap justify-end gap-1.5">
                        {allocationBadge(property.is_allocation)}
                        {activeBadge(property.is_actif)}
                    </div>
                </div>

                <div className="flex items-center gap-2.5 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2">
                    <AvatarName name={property.proprietaire?.name} />
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold text-[#0f172a]">{property.proprietaire?.name}</p>
                        <p className="truncate text-xs text-[#5f7182]">
                            {property.type?.name}
                            {property.proprietaire?.tel1 ? ` · ${property.proprietaire.tel1}` : ''}
                        </p>
                    </div>
                </div>

                <div className="flex gap-6">
                    <div>
                        <p className="text-[15px] font-bold text-[#0f172a]">{property.batiments_count}</p>
                        <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">
                            {property.batiments_count > 1 ? 'Bâtiments' : 'Bâtiment'}
                        </p>
                    </div>
                    <div>
                        <p className="text-[15px] font-bold text-[#0f172a]">{property.portes_libres}</p>
                        <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">Portes libres</p>
                    </div>
                    <div>
                        <p className="text-[15px] font-bold text-[#0f172a]">{property.portes_total}</p>
                        <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">Portes totales</p>
                    </div>
                </div>

                <div className="flex flex-col gap-1.5">
                    <div className="flex justify-between text-xs text-[#5f7182]">
                        <span>Taux d&apos;occupation</span>
                        <strong className="text-[#0f172a]">{property.progress}%</strong>
                    </div>
                    <div className="h-1.5 w-full overflow-hidden rounded-full bg-[#f1f5f9]">
                        <div className="h-full rounded-full" style={{ width: `${property.progress}%`, backgroundColor: property.progress >= 70 ? COLORS.green : property.progress <= 25 ? COLORS.amber : COLORS.blue }} />
                    </div>
                </div>

                <div className="mt-1 flex gap-2">
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className={cn('flex-1', agenceButtonStyles.actionBlue)}
                    >
                        <Link href={`/agence/proprietes/show/${property.id}`}>
                            <Eye className="h-4 w-4" /> Voir
                        </Link>
                    </Button>
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className={cn('flex-1', agenceButtonStyles.actionGreen)}
                    >
                        <Link href={`/agence/proprietes/edit/${property.id}`}>
                            <Pencil className="h-4 w-4" /> Modifier
                        </Link>
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        className={cn('flex-1', agenceButtonStyles.danger)}
                        onClick={() => onDisable(property)}
                    >
                        <Power className="h-4 w-4" /> Désactiver
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}

function ReferentielCard({ title, icon: Icon, items, onCreate, onEdit, onDelete }) {
    return (
        <Card className="overflow-hidden rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between border-b border-[#e2e8f0] py-3.5">
                <CardTitle className="text-sm text-[#0f172a]">{title}</CardTitle>
                <div className="flex items-center gap-2">
                    <span className="rounded-full bg-[#f1f5f9] px-2 py-0.5 text-[11px] font-semibold text-[#5f7182]">
                        {items.length}
                    </span>
                    {onCreate ? (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            className={agenceButtonStyles.actionBlue}
                            onClick={onCreate}
                        >
                            <Plus className="h-3.5 w-3.5" />
                            Ajouter
                        </Button>
                    ) : null}
                </div>
            </CardHeader>
            <CardContent className="mt-2 p-2">
                {items.length === 0 ? (
                    <EmptyState compact title="Référentiel vide" desc="Aucun élément n'a encore été ajouté." />
                ) : (
                    <ScrollArea className="max-h-72">
                        <ul className="flex flex-col gap-0.5">
                            {items.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex items-center justify-between gap-3 rounded-lg px-2.5 py-2 text-sm hover:bg-[#f8fafc]"
                                >
                                    <div className="flex min-w-0 items-center gap-2.5">
                                        <span className="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-md bg-[#eaf4fb] text-[#00559b]">
                                            <Icon className="h-3.5 w-3.5" />
                                        </span>
                                        <div className="min-w-0">
                                            <p className="truncate text-[#0f172a]">{item.name}</p>
                                            {item.description ? (
                                                <p className="truncate text-xs text-[#94a3b8]">{item.description}</p>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div className="flex shrink-0 items-center gap-1">
                                        {onEdit ? (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                        className={agenceButtonStyles.actionGreenIcon}
                                                onClick={() => onEdit(item)}
                                            >
                                                <Pencil className="h-4 w-4" />
                                            </Button>
                                        ) : null}
                                        {onDelete ? (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                className={agenceButtonStyles.actionRedIcon}
                                                onClick={() => onDelete(item)}
                                            >
                                                <X className="h-4 w-4" />
                                            </Button>
                                        ) : null}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </ScrollArea>
                )}
            </CardContent>
        </Card>
    );
}

function ConfirmDisableDialog({ property, onCancel, onConfirm }) {
    return (
        <Dialog open={Boolean(property)} onOpenChange={(open) => !open && onCancel()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#fef2f2] text-[#b42318]">
                        <TriangleAlert className="h-5 w-5" />
                    </div>
                    <DialogTitle>Désactiver cette propriété ?</DialogTitle>
                    <DialogDescription>
                        <span className="font-mono text-[#0f172a]">{property?.reference}</span> ne sera plus visible dans les recherches
                        actives. Vous pourrez la réactiver à tout moment.
                    </DialogDescription>
                </DialogHeader>

                <Separator />

                <DialogFooter>
                    <Button variant="outline" className={agenceButtonStyles.outline} onClick={onCancel}>
                        Annuler
                    </Button>
                    <Button variant="destructive" className={agenceButtonStyles.danger} onClick={() => onConfirm(property)}>
                        Désactiver
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function ReferentielDialog({ openState, title, submitLabel, onClose, onSubmit }) {
    const isEditing = openState.mode === 'edit';
    const [name, setName] = useState(openState.item?.name ?? '');
    const [description, setDescription] = useState(openState.item?.description ?? '');

    useEffect(() => {
        setName(openState.item?.name ?? '');
        setDescription(openState.item?.description ?? '');
    }, [openState.item, openState.mode]);

    return (
        <Dialog open={Boolean(openState.kind)} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>
                        {isEditing ? 'Modifie les informations du référentiel.' : 'Crée un nouvel élément de référentiel.'}
                    </DialogDescription>
                </DialogHeader>

                <form
                    className="space-y-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        onSubmit({ id: openState.item?.id, name, description });
                    }}
                >
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-[#0f172a]">Libellé *</label>
                        <Input
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder="Ex: Piscine, Bureau, École…"
                            required
                        />
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium text-[#0f172a]">Description</label>
                        <textarea
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                            rows={4}
                            className="min-h-24 w-full rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none placeholder:text-[#94a3b8] focus:border-[#00559b] focus:ring-2 focus:ring-[#00559b]/20"
                            placeholder="Description facultative"
                        />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" className={agenceButtonStyles.outline} onClick={onClose}>
                            Annuler
                        </Button>
                        <Button type="submit" className={agenceButtonStyles.primary}>
                            {submitLabel}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function MetricCard({ label, value, foot, icon: Icon, accent }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardDescription className="text-sm font-medium text-[#5f7182]">{label}</CardDescription>
                <span className={cn('flex h-10 w-10 items-center justify-center rounded-xl', accent)}>
                    <Icon className="h-5 w-5" />
                </span>
            </CardHeader>
            <CardContent>
                <p className="text-2xl font-semibold text-[#0f172a]">{value}</p>
                {foot ? <p className="mt-1 text-xs text-[#94a3b8]">{foot}</p> : null}
            </CardContent>
        </Card>
    );
}

export default function Proprietes({
    proprietes = {},
    stats = {},
    filters = {},
    types = [],
    equipements = [],
    proximites = [],
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [allocationFilter, setAllocationFilter] = useState(
        filters.is_allocation === '' || filters.is_allocation === null || filters.is_allocation === undefined
            ? '__all__'
            : normalizeAllocationFilter(filters.is_allocation)
    );
    const [activeFilter, setActiveFilter] = useState(
        filters.is_actif === '' || filters.is_actif === null || filters.is_actif === undefined
            ? 'active'
            : filters.is_actif === false || filters.is_actif === 0 || filters.is_actif === '0' || filters.is_actif === 'false'
                ? 'inactive'
                : 'active'
    );
    const [tab, setTab] = useState(normalizeAllocationFilter(filters.is_allocation));
    const [toDisable, setToDisable] = useState(null);
    const [refDialog, setRefDialog] = useState({ kind: '', mode: 'create', item: null });
    const [showRefActions, setShowRefActions] = useState(false);
    const didMountRef = useRef(false);

    const propertyList = useMemo(() => {
        const source = Array.isArray(proprietes?.data) ? proprietes.data : [];
        return source.map(normalizeProperty);
    }, [proprietes]);

    const typeOptions = useMemo(() => {
        const source = Array.isArray(types) && types.length > 0 ? types : DEMO_TYPES;
        return source
            .map((item) => ({
                value: String(item?.id ?? item?.type_propriete_id ?? item?.name ?? ''),
                label: item?.name ?? String(item),
                raw: normalizeReferenceItem(item),
            }))
            .filter((item) => item.value && item.label);
    }, [types]);

    const referentielEquipements = useMemo(() => {
        const source = Array.isArray(equipements) && equipements.length > 0 ? equipements : DEMO_REFERENTIELS.equipements;
        return source.map((item) => normalizeReferenceItem(item));
    }, [equipements]);

    const referentielProximites = useMemo(() => {
        const source = Array.isArray(proximites) && proximites.length > 0 ? proximites : DEMO_REFERENTIELS.proximites;
        return source.map((item) => normalizeReferenceItem(item));
    }, [proximites]);

    const activeProperties = useMemo(() => propertyList.filter((property) => property.is_actif), [propertyList]);
    const allocationCount = useMemo(() => Number(stats.allocation ?? 0), [stats.allocation]);
    const nonAllocationCount = useMemo(() => Number(stats.non_allocation ?? 0), [stats.non_allocation]);
    const allocationProperties = useMemo(() => propertyList.filter((property) => property.is_allocation), [propertyList]);
    const nonAllocationProperties = useMemo(() => propertyList.filter((property) => !property.is_allocation), [propertyList]);
    const allocationFilterLabel = useMemo(() => {
        if (allocationFilter === 'allocation') return 'En location';
        if (allocationFilter === 'vente') return 'En vente';
        return '';
    }, [allocationFilter]);

    const activeFilterLabel = useMemo(() => {
        if (activeFilter === 'active') return 'Actives';
        if (activeFilter === 'inactive') return 'Inactives';
        if (activeFilter === 'all') return 'Toutes les propriétés';
        return '';
    }, [activeFilter]);

    useEffect(() => {
        if (!didMountRef.current) {
            didMountRef.current = true;
            return undefined;
        }

        const timeoutId = window.setTimeout(() => {
            const query = {};
            const trimmedSearch = search.trim();

            if (trimmedSearch) query.search = trimmedSearch;
            if (allocationFilter !== '__all__') {
                query.is_allocation = allocationFilter === 'allocation' ? '1' : '0';
            } else if (tab === 'allocation') {
                query.is_allocation = '1';
            } else if (tab === 'vente') {
                query.is_allocation = '0';
            }

            if (activeFilter === 'inactive') {
                query.is_actif = '0';
            } else if (activeFilter === 'all') {
                query.is_actif = 'all';
            } else if (activeFilter === 'active') {
                query.is_actif = '1';
            }

            router.get('/agence/proprietes', query, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [search, allocationFilter, activeFilter, tab]);

    const resetFilters = () => {
        setSearch('');
        setAllocationFilter('__all__');
        setActiveFilter('all');
        setTab('all');
    };

    const confirmDisable = (property) => {
        if (!property) return;

        router.delete(`/agence/proprietes/destroy/${property.id}`, {
            preserveScroll: true,
            onSuccess: () => setToDisable(null),
            onError: () => setToDisable(null),
        });
    };

    const openRefDialog = (kind, mode = 'create', item = null) => {
        setRefDialog({ kind, mode, item });
    };

    const closeRefDialog = () => {
        setRefDialog({ kind: '', mode: 'create', item: null });
    };

    const submitRefDialog = ({ id, name, description }) => {
        const payload = { name: name.trim(), description: description?.trim() || '' };

        if (refDialog.kind === 'type') {
            if (refDialog.mode === 'edit') {
                router.put(`/agence/types-propriete/${id}`, payload, {
                    preserveScroll: true,
                    onSuccess: closeRefDialog,
                });
            } else {
                router.post('/agence/types-propriete', payload, {
                    preserveScroll: true,
                    onSuccess: closeRefDialog,
                });
            }
            return;
        }

        if (refDialog.kind === 'equipement') {
            if (refDialog.mode === 'edit') {
                router.put(`/agence/equipement-propriete/${id}`, payload, {
                    preserveScroll: true,
                    onSuccess: closeRefDialog,
                });
            } else {
                router.post('/agence/equipement-propriete', payload, {
                    preserveScroll: true,
                    onSuccess: closeRefDialog,
                });
            }
            return;
        }

        if (refDialog.kind === 'proximite') {
            if (refDialog.mode === 'edit') {
                router.put(`/agence/possimite-propriete/${id}`, payload, {
                    preserveScroll: true,
                    onSuccess: closeRefDialog,
                });
            } else {
                router.post('/agence/possimite-propriete', payload, {
                    preserveScroll: true,
                    onSuccess: closeRefDialog,
                });
            }
        }
    };

    const deleteRefItem = (kind, item) => {
        if (!window.confirm(`Supprimer ${item.name} ?`)) {
            return;
        }

        const url =
            kind === 'type'
                ? `/agence/types-propriete/${item.id}`
                : kind === 'equipement'
                    ? `/agence/equipement-propriete/${item.id}`
                    : `/agence/possimite-propriete/${item.id}`;

        router.delete(url, {
            preserveScroll: true,
        });
    };

    const kpis = [
        {
            label: 'Total propriétés',
            value: number(stats.total ?? activeProperties.length),
            foot: "Sur l'ensemble du portefeuille",
            icon: Home,
            accent: 'bg-[#f1f5f9] text-[#0f172a]',
        },
        {
            label: 'En location',
            value: number(stats.allocation ?? allocationCount),
            foot: 'Destinées à la location',
            icon: Building2,
            accent: 'bg-[#eaf4fb] text-[#00559b]',
        },
        {
            label: 'En vente',
            value: number(stats.non_allocation ?? nonAllocationCount),
            foot: 'Actuellement en vente',
            icon: DoorOpen,
            accent: 'bg-[#fffbeb] text-[#b45309]',
        },
        {
            label: 'Ce mois',
            value: number(stats.ce_mois ?? 0),
            foot: 'Créées sur le mois courant',
            icon: CalendarClock,
            accent: 'bg-[#eef8df] text-[#4d8500]',
        },
    ];

    const tabs = [
        { value: 'all', label: 'Toutes les propriétés', badge: Number(stats.total ?? propertyList.length) },
        { value: 'allocation', label: 'Location', badge: allocationCount },
        { value: 'vente', label: 'Vente', badge: nonAllocationCount },
        { value: 'referentiels', label: 'Référentiels' },
    ];

    const propertyColumns = useMemo(
        () => [
            {
                id: 'proprietaire',
                accessorFn: (row) => row.proprietaire?.name ?? '',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Propriétaire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                enableHiding: false,
                meta: { label: 'Propriétaire' },
                cell: ({ row }) => {
                    const property = row.original;

                    return (
                        <div className="flex items-center gap-2.5">
                            <AvatarName name={property.proprietaire?.name} />
                            <div>
                                <p className="text-sm font-semibold text-[#0f172a]">{property.proprietaire?.name}</p>
                                <p className="text-xs text-[#94a3b8]">{property.proprietaire?.tel1 || 'N/A'}</p>
                            </div>
                        </div>
                    );
                },
            },
            {
                accessorKey: 'adresse_complete',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Adresse"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Adresse' },
                cell: ({ row }) => (
                    <p className="text-sm text-[#0f172a]">{row.original.adresse_complete}</p>
                ),
            },
            {
                accessorKey: 'batiments_count',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Bâtiments"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                cell: ({ row }) => <span className="text-sm tabular-nums text-[#0f172a]">{row.original.batiments_count}</span>,
                meta: { label: 'Bâtiments', cellClassName: 'whitespace-nowrap' },
            },
            {
                id: 'portes',
                accessorFn: (row) => row.portes_libres,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Portes libres"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Portes libres / total', cellClassName: 'whitespace-nowrap' },
                cell: ({ row }) => (
                    <span className="text-sm tabular-nums text-[#0f172a]">
                        <span className="font-bold text-[#76c206]">{row.original.portes_libres}</span> / {row.original.portes_total}
                    </span>
                ),
            },
            {
                id: 'statut',
                header: 'Statut',
                meta: { label: 'Statut' },
                cell: ({ row }) => (
                    <div className="flex flex-wrap gap-1.5">
                        {allocationBadge(row.original.is_allocation)}
                        {activeBadge(row.original.is_actif)}
                    </div>
                ),
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', disableExport: true },
                cell: ({ row }) => (
                    <div className="flex justify-end gap-1.5">
                        <Button
                            asChild
                            variant="outline"
                            size="icon"
                        className={agenceButtonStyles.actionBlueIcon}
                        >
                            <Link href={`/agence/proprietes/show/${row.original.id}`}>
                                <Eye className="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            asChild
                            variant="outline"
                            size="icon"
                            className={agenceButtonStyles.actionGreenIcon}
                        >
                            <Link href={`/agence/proprietes/edit/${row.original.id}`}>
                                <Pencil className="h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                ),
                meta: { cellClassName: 'text-right whitespace-nowrap' },
            },
        ],
        []
    );

    const activeFilters = [search.trim() ? `"${search.trim()}"` : null, allocationFilterLabel || null, activeFilterLabel || null].filter(Boolean);
    const hasPagination = Array.isArray(proprietes?.links) && proprietes.links.length > 3;
    const pageLinks = hasPagination ? proprietes.links : null;
    const handlePageChange = (url) => {
        if (!url) return;

        router.get(url, {}, { preserveScroll: true, preserveState: true, replace: true });
    };

    return (
        <AgenceLayout title="Propriétés">
            <Head title="Propriétés" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Gestion des propriétés</h2>
                     
                    </div>

                    <Button asChild className={agenceButtonStyles.primary}>
                        <Link href="/agence/proprietes/create">
                            <Plus className="h-4 w-4" /> Nouvelle propriété
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {kpis.map((kpi) => (
                        <MetricCard key={kpi.label} {...kpi} />
                    ))}
                </div>

                <Tabs value={tab} onValueChange={setTab} className="space-y-6">
                    <TabsList className="inline-flex !h-auto w-full flex-wrap justify-start gap-1 rounded-xl border border-[#c8d4de] bg-[#f1f5f9] p-1">
                        {tabs.map((item) => (
                            <TabsTrigger key={item.value} value={item.value} className="rounded-lg px-4 py-2 text-sm font-semibold">
                                <span className="flex items-center gap-2">
                                    {item.label}
                                    {typeof item.badge === 'number' ? (
                                        <Badge variant="outline" className="h-5 rounded-full border-[#c8d4de] px-1.5 py-0 text-[11px] font-bold text-[#5f7182]">
                                            {item.badge}
                                        </Badge>
                                    ) : null}
                                </span>
                            </TabsTrigger>
                        ))}
                    </TabsList>

                    <TabsContent value="all" className="m-0">
                        <DataTable
                            title="Catalogue des propriétés"
                            columns={propertyColumns}
                            data={propertyList}
                            pagination={pageLinks ? proprietes : null}
                            onPageChange={handlePageChange}
                            searchValue={search}
                            onSearchChange={setSearch}
                            searchPlaceholder="Rechercher par référence, adresse ou propriétaire"
                            filtersSlot={
                                <div className="grid w-full grid-cols-1 gap-3 lg:min-w-[520px] lg:grid-cols-2">
                                    <div className="w-full min-w-0">
                                        <Select value={allocationFilter} onValueChange={setAllocationFilter} className="w-full">
                                            <SelectTrigger className="h-9 w-full rounded-xl">
                                                <SelectValue placeholder="Statut location" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="__all__">Toutes les offres</SelectItem>
                                                <SelectItem value="allocation">En location</SelectItem>
                                                <SelectItem value="vente">En vente</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="w-full min-w-0">
                                        <Select value={activeFilter} onValueChange={setActiveFilter} className="w-full">
                                            <SelectTrigger className="h-9 w-full rounded-xl">
                                                <SelectValue placeholder="Statut actif" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Actives</SelectItem>
                                                <SelectItem value="inactive">Inactives</SelectItem>
                                                <SelectItem value="all">Toutes les propriétés</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            }
                            onResetFilters={resetFilters}
                            emptyState={
                                <EmptyState
                                    title="Aucune propriété disponible"
                                    desc={activeFilters.length > 0 ? 'Aucun résultat ne correspond aux filtres appliqués.' : 'Aucune propriété n’a encore été créée.'}
                                />
                            }
                        />
                    </TabsContent>

                    <TabsContent value="allocation" className="m-0">
                        {allocationProperties.length === 0 ? (
                            <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                <EmptyState
                                    title="Aucune propriété en location"
                                   desc={null}
                                    onReset={resetFilters}
                                />
                            </Card>
                        ) : (
                            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                {allocationProperties.map((property) => (
                                    <PropertyCard key={property.id} property={property} onDisable={setToDisable} />
                                ))}
                            </div>
                        )}
                    </TabsContent>

                    <TabsContent value="vente" className="m-0">
                        {nonAllocationProperties.length === 0 ? (
                            <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                <EmptyState
                                    title="Aucune propriété en vente"
                                    desc={null}
                                    onReset={resetFilters}
                                />
                            </Card>
                        ) : (
                            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                {nonAllocationProperties.map((property) => (
                                    <PropertyCard key={property.id} property={property} onDisable={setToDisable} />
                                ))}
                            </div>
                        )}
                    </TabsContent>

                    <TabsContent value="referentiels" className="m-0">
                        <div className="flex flex-col gap-3">
                            <div className="flex items-center justify-between rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-3">
                                <div className="min-w-0">
                                    <p className="text-sm font-semibold text-[#0f172a]">Gestion des référentiels</p>
                                 
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    className={cn(agenceButtonStyles.outline, 'shrink-0')}
                                    onClick={() => setShowRefActions((value) => !value)}
                                >
                                    <Settings2 className="h-4 w-4" />
                                    {showRefActions ? 'Masquer les actions' : 'Afficher les actions'}
                                </Button>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <ReferentielCard
                                    title="Types de propriété"
                                    icon={Building2}
                                    items={typeOptions.map((type) => type.raw)}
                                    onCreate={showRefActions ? () => openRefDialog('type', 'create') : null}
                                    onEdit={showRefActions ? (item) => openRefDialog('type', 'edit', item) : null}
                                    onDelete={showRefActions ? (item) => deleteRefItem('type', item) : null}
                                />
                                <ReferentielCard
                                    title="Équipements"
                                    icon={Tag}
                                    items={referentielEquipements}
                                    onCreate={showRefActions ? () => openRefDialog('equipement', 'create') : null}
                                    onEdit={showRefActions ? (item) => openRefDialog('equipement', 'edit', item) : null}
                                    onDelete={showRefActions ? (item) => deleteRefItem('equipement', item) : null}
                                />
                                <ReferentielCard
                                    title="Proximités"
                                    icon={MapPin}
                                    items={referentielProximites}
                                    onCreate={showRefActions ? () => openRefDialog('proximite', 'create') : null}
                                    onDelete={showRefActions ? (item) => deleteRefItem('proximite', item) : null}
                                />
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>

            <ConfirmDisableDialog property={toDisable} onCancel={() => setToDisable(null)} onConfirm={confirmDisable} />
            <ReferentielDialog
                openState={refDialog}
                title={
                    refDialog.kind === 'type'
                        ? refDialog.mode === 'edit'
                            ? 'Modifier le type'
                            : 'Nouveau type de propriété'
                        : refDialog.kind === 'equipement'
                            ? refDialog.mode === 'edit'
                                ? "Modifier l'équipement"
                                : 'Nouvel équipement'
                            : refDialog.mode === 'edit'
                                ? 'Modifier la proximité'
                                : 'Nouvelle proximité'
                }
                submitLabel={refDialog.mode === 'edit' ? 'Mettre à jour' : 'Enregistrer'}
                onClose={closeRefDialog}
                onSubmit={submitRefDialog}
            />
        </AgenceLayout>
    );
}
