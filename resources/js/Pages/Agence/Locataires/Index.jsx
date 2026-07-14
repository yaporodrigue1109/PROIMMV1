import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Eye,
    Home,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Plus,
    Power,
    UserRound,
    X,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { DataTable } from '../../../components/ui/data-table';
import { DataTableColumnHeader } from '../../../components/ui/data-table-column-header';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
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

const formatDate = (value) => {
    if (!value) return '—';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
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

function asArray(value) {
    return Array.isArray(value) ? value : [];
}

function normalizeContract(contrat) {
    return {
        id: contrat?.locataire_agence_id ?? contrat?.id ?? crypto.randomUUID(),
        is_active: Boolean(contrat?.is_active),
        proprietaire: {
            name: contrat?.proprietaire?.name ?? 'N/A',
            tel1: contrat?.proprietaire?.tel1 ?? '',
        },
        propriete: {
            reference: contrat?.propriete?.reference ?? 'N/A',
            adresse_complete: contrat?.propriete?.adresse_complete ?? '',
        },
        batiment: {
            name: contrat?.batiment?.name ?? 'N/A',
        },
        lot: {
            name: contrat?.lot?.name ?? '',
        },
        porte: {
            numero_porte: contrat?.porte?.numero_porte ?? 'N/A',
        },
        date_debut_bail: contrat?.date_debut_bail ?? null,
        date_fin_bail: contrat?.date_fin_bail ?? null,
    };
}

function normalizeLocataire(locataire) {
    const contrats = asArray(locataire?.contrats).map(normalizeContract);
    const contratActif = contrats.find((item) => item.is_active) ?? contrats[0] ?? null;

    return {
        id: locataire?.locataire_id ?? locataire?.id ?? crypto.randomUUID(),
        name: locataire?.name ?? 'N/A',
        code: locataire?.code ?? 'N/A',
        tel1: locataire?.tel1 ?? '',
        tel2: locataire?.tel2 ?? '',
        email: locataire?.email ?? '',
        adresse: locataire?.adresse ?? '',
        region: locataire?.region?.name ?? '',
        ville: locataire?.ville?.name ?? '',
        num_piece: locataire?.num_piece ?? '',
        type_piece: locataire?.type_piece?.name ?? locataire?.type_piece ?? '',
        contrats,
        contrats_count: contrats.length,
        contrat_actif: contratActif,
    };
}

function normalizePropertyOption(property) {
    return {
        value: String(property?.propriete_id ?? property?.id ?? ''),
        label: property?.reference ?? property?.name ?? 'N/A',
    };
}

function AvatarName({ name }) {
    return (
        <Avatar className="h-10 w-10 border border-[#c8d4de] bg-[#eaf4fb]">
            <AvatarFallback className="bg-[#eaf4fb] text-[11px] font-bold text-[#00559b]">
                {initials(name)}
            </AvatarFallback>
        </Avatar>
    );
}

function EmptyState({ title, desc, onReset, compact = false }) {
    return (
        <div className={cn('flex flex-col items-center justify-center gap-3 text-center', compact ? 'px-4 py-8' : 'px-6 py-14')}>
            <span className="flex h-12 w-12 items-center justify-center rounded-full bg-[#f1f5f9] p-3 text-[#94a3b8]">
                <UserRound className="h-6 w-6" />
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

function StatCard({ icon: Icon, label, value, accent = COLORS.blue, tint = '#eaf4fb' }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardContent className="mt-6 flex items-center gap-3 p-4">
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

export default function Index({
    locataires = null,
    stats = {},
    proprietes = [],
    filters = {},
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [proprieteId, setProprieteId] = useState(filters.propriete_id ?? '__all__');
    const [isActif, setIsActif] = useState(filters.is_actif ?? '__all__');
    const didMountRef = useRef(false);

    const rows = useMemo(() => asArray(locataires?.data).map(normalizeLocataire), [locataires?.data]);

    const propertyOptions = useMemo(
        () => (Array.isArray(proprietes) ? proprietes.map(normalizePropertyOption) : []),
        [proprietes]
    );

    const selectedPropertyLabel =
        propertyOptions.find((item) => item.value === proprieteId)?.label ?? 'Toutes les propriétés';

    const selectedStatusLabel =
        isActif === '1' ? 'Actifs' : isActif === '0' ? 'Résiliés' : 'Tous les statuts';

    const activeFilters = [
        search.trim() ? `"${search.trim()}"` : null,
        proprieteId !== '__all__' ? selectedPropertyLabel : null,
        isActif !== '__all__' ? selectedStatusLabel : null,
    ].filter(Boolean);

    const applyFilters = (next = {}) => {
        const query = {
            search: next.search ?? search,
            propriete_id: next.propriete_id ?? proprieteId,
            is_actif: next.is_actif ?? isActif,
        };

        Object.keys(query).forEach((key) => {
            if (query[key] === '' || query[key] === '__all__' || query[key] === null || query[key] === undefined) {
                delete query[key];
            }
        });

        router.get('/agence/locataires', query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    useEffect(() => {
        if (!didMountRef.current) {
            didMountRef.current = true;
            return undefined;
        }

        const timeoutId = window.setTimeout(() => {
            const query = {};
            const trimmedSearch = search.trim();

            if (trimmedSearch) query.search = trimmedSearch;
            if (proprieteId !== '__all__') query.propriete_id = proprieteId;
            if (isActif !== '__all__') query.is_actif = isActif;

            router.get('/agence/locataires', query, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [search, proprieteId, isActif]);

    const resetFilters = () => {
        setSearch('');
        setProprieteId('__all__');
        setIsActif('__all__');

        router.get('/agence/locataires', {}, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const handlePageChange = (url) => {
        if (!url) return;

        router.get(url, {}, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const handleResilier = (locataire) => {
        if (!window.confirm(`Résilier le contrat de ${locataire.name} ?`)) {
            return;
        }

        router.patch(`/agence/locataires/${locataire.id}/resilier`, {}, {
            preserveScroll: true,
        });
    };

    const kpis = [
        {
            label: 'Total locataires',
            value: number(stats.total ?? rows.length),
            icon: UserRound,
            accent: COLORS.blue,
            tint: '#eaf4fb',
        },
        {
            label: 'Contrats actifs',
            value: number(stats.actifs ?? 0),
            icon: Home,
            accent: COLORS.greenDark,
            tint: '#eef8df',
        },
        {
            label: 'Contrats résiliés',
            value: number(stats.resilies ?? 0),
            icon: X,
            accent: '#b42318',
            tint: '#fff4f4',
        },
        {
            label: 'Ce mois',
            value: number(stats.ce_mois ?? 0),
            icon: MapPin,
            accent: COLORS.amber,
            tint: '#fffbeb',
        },
    ];

    const columns = useMemo(
        () => [
            {
                id: 'name',
                accessorFn: (row) => row.name,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Locataire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Locataire' },
                cell: ({ row }) => {
                    const locataire = row.original;

                    return (
                        <div className="flex items-center gap-3">
                            <AvatarName name={locataire.name} />
                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-[#0f172a]">{locataire.name}</p>
                                <p className="font-mono text-xs text-[#94a3b8]">{locataire.code}</p>
                                <div className="mt-1 flex flex-wrap gap-1.5">
                                    <Badge variant="outline" className="rounded-full border-[#c8d4de] text-[11px]">
                                        {number(locataire.contrats_count)} contrat{locataire.contrats_count > 1 ? 's' : ''}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    );
                },
            },
            {
                id: 'contact',
                accessorFn: (row) => row.tel1,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Contact"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Contact' },
                cell: ({ row }) => {
                    const locataire = row.original;

                    return (
                        <div className="space-y-1 text-sm text-[#0f172a]">
                            <div className="flex items-center gap-2">
                                <Phone className="h-4 w-4 shrink-0 text-[#5f7182]" />
                                <span>{locataire.tel1 || '—'}</span>
                            </div>
                            {locataire.tel2 ? (
                                <div className="flex items-center gap-2 text-[#5f7182]">
                                    <Phone className="h-4 w-4 shrink-0" />
                                    <span>{locataire.tel2}</span>
                                </div>
                            ) : null}
                            {locataire.email ? (
                                <div className="flex items-center gap-2 text-[#5f7182]">
                                    <Mail className="h-4 w-4 shrink-0" />
                                    <span className="truncate">{locataire.email}</span>
                                </div>
                            ) : null}
                        </div>
                    );
                },
            },
            {
                id: 'contrat',
                accessorFn: (row) => row.contrat_actif?.proprietaire?.name ?? '',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Contrat actif"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Contrat actif' },
                cell: ({ row }) => {
                    const locataire = row.original;
                    const contrat = locataire.contrat_actif;

                    if (!contrat) {
                        return <span className="text-sm text-[#cbd5e1]">Sans contrat</span>;
                    }

                    return (
                        <div className="space-y-1 text-sm">
                            <p className="font-semibold text-[#0f172a]">{contrat.proprietaire?.name ?? '—'}</p>
                            <p className="text-xs text-[#5f7182]">
                                {contrat.date_debut_bail ? `Début: ${formatDate(contrat.date_debut_bail)}` : 'Début non précisé'}
                            </p>
                            <p className="text-xs text-[#5f7182]">
                                {contrat.date_fin_bail ? `Fin: ${formatDate(contrat.date_fin_bail)}` : 'Fin non précisée'}
                            </p>
                        </div>
                    );
                },
            },
            {
                id: 'bien',
                accessorFn: (row) => row.contrat_actif?.propriete?.reference ?? '',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Bien"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Bien' },
                cell: ({ row }) => {
                    const contrat = row.original.contrat_actif;

                    if (!contrat) {
                        return <span className="text-sm text-[#cbd5e1]">—</span>;
                    }

                    return (
                        <div className="space-y-1 text-sm">
                            <p className="font-semibold text-[#0f172a]">{contrat.propriete?.reference ?? '—'}</p>
                            <p className="text-xs text-[#5f7182]">{contrat.propriete?.adresse_complete || 'Adresse non précisée'}</p>
                        </div>
                    );
                },
            },
            {
                id: 'logement',
                accessorFn: (row) => row.contrat_actif?.porte?.numero_porte ?? '',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Logement"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Logement' },
                cell: ({ row }) => {
                    const contrat = row.original.contrat_actif;

                    if (!contrat) {
                        return <span className="text-sm text-[#cbd5e1]">—</span>;
                    }

                    return (
                        <div className="space-y-1 text-sm">
                            <p className="font-semibold text-[#0f172a]">{contrat.batiment?.name ?? '—'}</p>
                            <p className="text-xs text-[#5f7182]">
                                Porte {contrat.porte?.numero_porte ?? '—'}
                                {contrat.lot?.name ? ` · ${contrat.lot.name}` : ''}
                            </p>
                        </div>
                    );
                },
            },
            {
                id: 'statut',
                header: 'Statut',
                meta: { label: 'Statut' },
                cell: ({ row }) => {
                    const contrat = row.original.contrat_actif;

                    if (!contrat) {
                        return (
                            <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                                Sans contrat
                            </Badge>
                        );
                    }

                    return contrat.is_active ? (
                        <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
                            Actif
                        </Badge>
                    ) : (
                        <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
                            Résilié
                        </Badge>
                    );
                },
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', disableExport: true },
                cell: ({ row }) => {
                    const locataire = row.original;
                    const contrat = locataire.contrat_actif;

                    return (
                        <div className="flex justify-end gap-1.5">
                            <Button
                                asChild
                                variant="outline"
                                size="icon"
                                className={agenceButtonStyles.actionBlueIcon}
                            >
                                <Link href={`/agence/locataires/${locataire.id}`}>
                                    <Eye className="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                asChild
                                variant="outline"
                                size="icon"
                                className={agenceButtonStyles.actionGreenIcon}
                            >
                                <Link href={`/agence/locataires/${locataire.id}/edit`}>
                                    <Pencil className="h-4 w-4" />
                                </Link>
                            </Button>

                            {contrat?.is_active ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    className={agenceButtonStyles.actionRedIcon}
                                    onClick={() => handleResilier(locataire)}
                                >
                                    <Power className="h-4 w-4" />
                                </Button>
                            ) : null}
                        </div>
                    );
                },
                meta: { cellClassName: 'text-right whitespace-nowrap' },
            },
        ],
        [handleResilier]
    );

    const pageLinks = Array.isArray(locataires?.links) && locataires.links.length > 3 ? locataires : null;

    return (
        <AgenceLayout title="Locataires">
            <Head title="Locataires" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm text-[#5f7182]">Suivi des occupants et contrats de location</p>
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Gestion des locataires</h2>
                    </div>

                    <Button asChild className={agenceButtonStyles.primary}>
                        <Link href="/agence/locataires/create">
                            <Plus className="h-4 w-4" /> Nouveau locataire
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {kpis.map((kpi) => {
                        const Icon = kpi.icon;

                        return (
                            <StatCard
                                key={kpi.label}
                                icon={Icon}
                                label={kpi.label}
                                value={kpi.value}
                                accent={kpi.accent}
                                tint={kpi.tint}
                            />
                        );
                    })}
                </div>

                <Card className="hidden overflow-hidden rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <CardTitle className="text-base text-[#0f172a]">Filtres</CardTitle>
                                <CardDescription className="text-sm text-[#5f7182]">
                                    Filtrez par nom, propriété ou statut.
                                </CardDescription>
                            </div>

                            {activeFilters.length ? (
                                <div className="flex flex-wrap gap-1.5">
                                    {activeFilters.map((filter) => (
                                        <Badge key={filter} variant="outline" className="rounded-full border-[#c8d4de] text-[11px]">
                                            {filter}
                                        </Badge>
                                    ))}
                                </div>
                            ) : null}
                        </div>
                    </CardHeader>

                    <CardContent className="p-4">
                        <form
                            className="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto]"
                            onSubmit={(event) => {
                                event.preventDefault();
                                applyFilters();
                            }}
                        >
                            <div className="relative">
                                <UserRound className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a3b8]" />
                                <Input
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Nom, code, téléphone, numéro de pièce..."
                                    className="h-10 rounded-xl border-[#c8d4de] pl-9"
                                />
                            </div>

                            <Select
                                value={proprieteId}
                                onValueChange={(value) => {
                                    setProprieteId(value);
                                    applyFilters({ propriete_id: value });
                                }}
                            >
                                <SelectTrigger className="h-10 rounded-xl border-[#c8d4de]">
                                    <SelectValue placeholder="Toutes les propriétés" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__all__">Toutes les propriétés</SelectItem>
                                    {propertyOptions.map((property) => (
                                        <SelectItem key={property.value} value={property.value}>
                                            {property.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select
                                value={isActif}
                                onValueChange={(value) => {
                                    setIsActif(value);
                                    applyFilters({ is_actif: value });
                                }}
                            >
                                <SelectTrigger className="h-10 rounded-xl border-[#c8d4de]">
                                    <SelectValue placeholder="Tous les statuts" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__all__">Tous les statuts</SelectItem>
                                    <SelectItem value="1">Actifs</SelectItem>
                                    <SelectItem value="0">Résiliés</SelectItem>
                                </SelectContent>
                            </Select>

                            <div className="flex items-center gap-2">
                                <Button type="submit" className={agenceButtonStyles.primary}>
                                    Filtrer
                                </Button>
                                <Button type="button" variant="outline" className={agenceButtonStyles.outline} onClick={resetFilters}>
                                    Réinitialiser
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <DataTable
                    title="Catalogue des locataires"
                   
                    columns={columns}
                    data={rows}
                    pagination={pageLinks}
                    onPageChange={handlePageChange}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Nom, code, téléphone, numéro de pièce..."
                    filtersSlot={
    <div className="flex items-center gap-2">
                                        <Select value={proprieteId} onValueChange={setProprieteId}>
                                            <SelectTrigger className="h-9 w-full md:w-[220px] rounded-xl">
                                                <SelectValue placeholder="Toutes les propriétés" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="__all__">Toutes les propriétés</SelectItem>
                                                {propertyOptions.map((property) => (
                                                    <SelectItem key={property.value} value={property.value}>
                                                        {property.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        <Select value={isActif} onValueChange={setIsActif}>
                                            <SelectTrigger className="h-9 w-full md:w-[170px] rounded-xl">
                                                <SelectValue placeholder="Tous les statuts" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="__all__">Tous les statuts</SelectItem>
                                                <SelectItem value="1">Actifs</SelectItem>
                                                <SelectItem value="0">Résiliés</SelectItem>
                                            </SelectContent>
                                        </Select>
                                  </div>

                                }
                    onResetFilters={resetFilters}
                    emptyState={
                        <EmptyState
                            title="Aucun locataire trouvé"
                            desc={
                                activeFilters.length > 0
                                    ? 'Aucun résultat ne correspond aux filtres appliqués.'
                                    : 'Aucun locataire n’a encore été enregistré.'
                            }
                            onReset={activeFilters.length ? resetFilters : null}
                        />
                    }
                />
            </div>
        </AgenceLayout>
    );
}
