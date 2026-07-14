import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Eye, Mail, MapPin, Pencil, Phone, Plus, Power, Trash2, UserRound, X } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent } from '../../../components/ui/card';
import { DataTable } from '../../../components/ui/data-table';
import { DataTableColumnHeader } from '../../../components/ui/data-table-column-header';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const COLORS = {
    blue: '#00559b',
    greenDark: '#4d8500',
    amber: '#b45309',
};

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

function initials(name) {
    return String(name ?? '')
        .split(' ')
        .filter(Boolean)
        .map((word) => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function currentPhotoUrl(proprietaire) {
    if (!proprietaire?.photo) {
        return '';
    }

    if (String(proprietaire.photo).startsWith('http')) {
        return proprietaire.photo;
    }

    return `/storage/${proprietaire.photo}`;
}

function normalizeProprietaire(proprietaire) {
    const liaison = Array.isArray(proprietaire?.agences) ? proprietaire.agences[0] ?? null : null;

    return {
        id: proprietaire?.proprietaire_id ?? proprietaire?.id ?? crypto.randomUUID(),
        name: proprietaire?.name ?? 'N/A',
        code: proprietaire?.code ?? 'N/A',
        photo: proprietaire?.photo ?? '',
        tel1: proprietaire?.tel1 ?? '',
        tel2: proprietaire?.tel2 ?? '',
        email: proprietaire?.email ?? '',
        adresse: proprietaire?.adresse ?? '',
        lotsCount: Number(proprietaire?.lots_count ?? 0),
        proprietesCount: Number(proprietaire?.proprietes_count ?? 0),
        liaison,
    };
}

function StatCard({ icon: Icon, label, value, accent = COLORS.blue, tint = '#eaf4fb' }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardContent className="mt-6 flex items-center gap-3 p-4">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style={{ backgroundColor: tint, color: accent }}>
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

export default function Index({ proprietaires = null, stats = {}, filters = {} }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const didMountRef = useRef(false);

    const rows = useMemo(
        () => (Array.isArray(proprietaires?.data) ? proprietaires.data.map(normalizeProprietaire) : []),
        [proprietaires?.data]
    );

    const selectedStatusLabel =
        status === 'active' ? 'Actifs' : status === 'inactive' ? 'Inactifs' : 'Tous les statuts';

    const activeFilters = [search.trim() ? `"${search.trim()}"` : null, status !== 'all' ? selectedStatusLabel : null].filter(Boolean);

    useEffect(() => {
        if (!didMountRef.current) {
            didMountRef.current = true;
            return undefined;
        }

        const timeoutId = window.setTimeout(() => {
            const query = {};
            const trimmedSearch = search.trim();

            if (trimmedSearch) query.search = trimmedSearch;
            if (status !== 'all') query.status = status;

            router.get('/agence/proprietaire', query, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [search, status]);

    const resetFilters = () => {
        setSearch('');
        setStatus('all');

        router.get('/agence/proprietaire', {}, {
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

    const handleDelete = (proprietaire) => {
        if (!window.confirm(`Confirmer la suppression de ${proprietaire.name} ?`)) return;

        router.delete(`/agence/proprietaire/${proprietaire.id}`, {
            preserveScroll: true,
        });
    };

    const handleToggleStatus = (liaison) => {
        if (!liaison?.proprietaire_agence_id) return;

        const isActive = Boolean(liaison.is_active);
        const route = isActive
            ? `/agence/proprietaire/${liaison.proprietaire_agence_id}/deactivate`
            : `/agence/proprietaire/${liaison.proprietaire_agence_id}/activate`;

        if (!window.confirm(isActive ? 'Désactiver ce propriétaire ?' : 'Activer ce propriétaire ?')) return;

        router.patch(route, {}, {
            preserveScroll: true,
        });
    };

    const columns = useMemo(
        () => [
            {
                id: 'proprietaire',
                accessorFn: (row) => row.name,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Propriétaire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Propriétaire' },
                cell: ({ row }) => {
                    const proprietaire = row.original;

                    return (
                        <div className="flex items-center gap-3">
                            <Avatar className="h-10 w-10 rounded-xl border border-[#c8d4de] bg-[#eaf4fb]">
                                {currentPhotoUrl(proprietaire) ? (
                                    <img
                                        src={currentPhotoUrl(proprietaire)}
                                        alt={`Photo de ${proprietaire.name}`}
                                        className="h-full w-full rounded-xl object-cover"
                                    />
                                ) : (
                                    <AvatarFallback className="rounded-xl bg-[#eaf4fb] text-[11px] font-bold text-[#00559b]">
                                        {initials(proprietaire.name)}
                                    </AvatarFallback>
                                )}
                            </Avatar>
                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-[#0f172a]">{proprietaire.name}</p>
                                <p className="font-mono text-xs text-[#94a3b8]">{proprietaire.code}</p>
                                
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
                    const proprietaire = row.original;

                    return (
                        <div className="space-y-1 text-sm text-[#0f172a]">
                            <div className="flex items-center gap-2">
                                <Phone className="h-4 w-4 shrink-0 text-[#5f7182]" />
                                <span>{proprietaire.tel1 || 'Non renseigné'}</span>
                            </div>
                            {proprietaire.tel2 ? (
                                <div className="flex items-center gap-2 text-[#5f7182]">
                                    <Phone className="h-4 w-4 shrink-0" />
                                    <span>{proprietaire.tel2}</span>
                                </div>
                            ) : null}
                            {proprietaire.email ? (
                                <div className="flex items-center gap-2 text-[#5f7182]">
                                    <Mail className="h-4 w-4 shrink-0" />
                                    <span className="truncate">{proprietaire.email}</span>
                                </div>
                            ) : null}
                        </div>
                    );
                },
            },
            {
                id: 'adresse',
                accessorFn: (row) => row.adresse,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Adresse"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Adresse' },
                cell: ({ row }) => {
                    const proprietaire = row.original;

                    return <p className="text-sm font-semibold text-[#0f172a]">{proprietaire.adresse || 'Non renseigné'}</p>;
                },
            },
            {
                id: 'representant',
                accessorFn: (row) => row.liaison?.name_representant ?? '',
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Représentant"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Représentant' },
                cell: ({ row }) => {
                    const liaison = row.original.liaison;

                    if (!liaison) {
                        return <span className="text-sm text-[#cbd5e1]">Non renseigné</span>;
                    }

                    return (
                        <div className="space-y-1 text-sm">
                            <p className="font-semibold text-[#0f172a]">{liaison.name_representant || 'Non renseigné'}</p>
                            <p className="text-xs text-[#5f7182]">{liaison.tel1_representant || 'Non renseigné'}</p>
                            {liaison.adresse_representant ? (
                                <p className="text-xs text-[#94a3b8]">{liaison.adresse_representant}</p>
                            ) : null}
                        </div>
                    );
                },
            },
            {
                id: 'statut',
                accessorFn: (row) => Boolean(row.liaison?.is_active),
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Statut"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Statut' },
                cell: ({ row }) => {
                    const liaison = row.original.liaison;

                    if (!liaison) {
                        return (
                            <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                                Sans liaison
                            </Badge>
                        );
                    }

                    return liaison.is_active ? (
                        <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
                            Actif
                        </Badge>
                    ) : (
                        <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
                            Inactif
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
                    const proprietaire = row.original;
                    const liaison = proprietaire.liaison;
                    const canDelete = proprietaire.lotsCount === 0 && proprietaire.proprietesCount === 0;

                    return (
                        <div className="flex justify-end gap-1.5">
                            <Button asChild variant="outline" size="icon" className={agenceButtonStyles.actionBlueIcon}>
                                <Link href={`/agence/proprietaire/${proprietaire.id}`}>
                                    <Eye className="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button asChild variant="outline" size="icon" className={agenceButtonStyles.actionGreenIcon}>
                                <Link href={`/agence/proprietaire/${proprietaire.id}/edit`}>
                                    <Pencil className="h-4 w-4" />
                                </Link>
                            </Button>

                            {liaison ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    className={liaison.is_active ? agenceButtonStyles.actionRedIcon : agenceButtonStyles.actionGreenIcon}
                                    onClick={() => handleToggleStatus(liaison)}
                                >
                                    <Power className="h-4 w-4" />
                                </Button>
                            ) : null}

                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                className={agenceButtonStyles.actionRedIcon}
                                onClick={canDelete ? () => handleDelete(proprietaire) : undefined}
                                disabled={!canDelete}
                                title={
                                    canDelete
                                        ? 'Supprimer le propriétaire'
                                        : 'Impossible de supprimer ce propriétaire tant qu’il a des lots ou des propriétés rattachés'
                                }
                            >
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        </div>
                    );
                },
                meta: { cellClassName: 'text-right whitespace-nowrap' },
            },
        ],
        [handleDelete, handleToggleStatus]
    );

    const pageLinks = Array.isArray(proprietaires?.links) && proprietaires.links.length > 3 ? proprietaires : null;

    return (
        <AgenceLayout title="Propriétaires">
            <Head title="Propriétaires" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                       
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Gestion des propriétaires</h2>
                    </div>

                    <Button asChild className={agenceButtonStyles.primary}>
                        <Link href="/agence/proprietaire/create">
                            <Plus className="h-4 w-4" /> Nouveau propriétaire
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard icon={UserRound} label="Total propriétaires" value={number(stats.total ?? proprietaires?.total ?? 0)} />
                    <StatCard icon={Power} label="Comptes actifs" value={number(stats.actifs ?? 0)} accent={COLORS.greenDark} tint="#eef8df" />
                    <StatCard icon={X} label="Comptes inactifs" value={number(stats.inactifs ?? 0)} accent="#b42318" tint="#fff4f4" />
                    <StatCard icon={MapPin} label="Ce mois" value={number(stats.ce_mois ?? 0)} accent={COLORS.amber} tint="#fffbeb" />
                </div>

                <DataTable
                    title="Catalogue des propriétaires"
                   
                    columns={columns}
                    data={rows}
                    pagination={pageLinks}
                    onPageChange={handlePageChange}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Nom, code, téléphone, email, adresse..."
                    filtersSlot={
                        <div className="flex items-center gap-2">
                            <Select value={status} onValueChange={setStatus}>
                                <SelectTrigger className="h-9 w-full rounded-xl md:w-[180px]">
                                    <SelectValue placeholder="Tous les statuts" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Tous les statuts</SelectItem>
                                    <SelectItem value="active">Actifs</SelectItem>
                                    <SelectItem value="inactive">Inactifs</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    }
                    onResetFilters={resetFilters}
                    emptyState={
                        <EmptyState
                            title="Aucun propriétaire trouvé"
                            desc={
                                activeFilters.length > 0
                                    ? 'Aucun résultat ne correspond aux filtres appliqués.'
                                    : 'Aucun propriétaire n’a encore été enregistré.'
                            }
                            onReset={activeFilters.length ? resetFilters : null}
                        />
                    }
                />
            </div>
        </AgenceLayout>
    );
}
