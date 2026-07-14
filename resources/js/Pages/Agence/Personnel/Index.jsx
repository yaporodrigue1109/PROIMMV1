import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    BriefcaseBusiness,
    Eye,
    Mail,
    Pencil,
    Phone,
    Plus,
    ShieldCheck,
    UserCheck,
    UserRound,
    UserX,
} from 'lucide-react';
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
    green: '#76c206',
    greenDark: '#4d8500',
    slate: '#5f7182',
    border: '#c8d4de',
    ink: '#0f172a',
    amber: '#b45309',
    red: '#b42318',
};

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const formatDate = (value) => {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

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

function normalizePersonnel(personnel) {
    const fullName = `${personnel?.nom ?? ''} ${personnel?.prenoms ?? ''}`.trim();

    return {
        id:
            personnel?.personnel_id ??
            personnel?.user_id ??
            personnel?.id_users ??
            personnel?.id ??
            crypto.randomUUID(),

        name:
            personnel?.name ??
            personnel?.nom_complet ??
            (fullName || 'N/A'),

        code:
            personnel?.code ??
            personnel?.matricule ??
            'N/A',

        matricule:
            personnel?.matricule ??
            personnel?.code ??
            'N/A',

        tel1:
            personnel?.tel1 ??
            personnel?.telephone ??
            personnel?.phone ??
            '',

        tel2:
            personnel?.tel2 ??
            personnel?.telephone_2 ??
            '',

        email:
            personnel?.email ??
            '',

        adresse:
            personnel?.adresse ??
            '',

        fonction:
            personnel?.fonction?.name ??
            personnel?.fonction?.libelle ??
            personnel?.fonction ??
            'Non définie',

        fonction_id:
            personnel?.fonction_id ??
            personnel?.fonction?.id ??
            '',

        role:
            personnel?.role?.name ??
            personnel?.role?.libelle ??
            personnel?.role ??
            'Personnel',

        date_embauche:
            personnel?.date_embauche ??
            personnel?.date_prise_service ??
            null,

        is_active:
            personnel?.is_active !== undefined
                ? Boolean(personnel.is_active)
                : personnel?.actif !== undefined
                    ? Boolean(personnel.actif)
                    : true,

        is_acces:
            personnel?.is_acces !== undefined
                ? Boolean(personnel.is_acces)
                : false,
    };
}

function normalizeRoleOption(role) {
    return {
        value: String(role?.role_id ?? role?.id ?? ''),
        label:
            role?.name ??
            role?.libelle ??
            role?.nom ??
            'N/A',
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
        <div
            className={cn(
                'flex flex-col items-center justify-center gap-3 text-center',
                compact ? 'px-4 py-8' : 'px-6 py-14'
            )}
        >
            <span className="flex h-12 w-12 items-center justify-center rounded-full bg-[#f1f5f9] p-3 text-[#94a3b8]">
                <UserRound className="h-6 w-6" />
            </span>

            <p className="text-sm font-semibold text-[#0f172a]">
                {title}
            </p>

            <p className="max-w-sm text-sm text-[#5f7182]">
                {desc}
            </p>

            {onReset ? (
                <Button
                    variant="outline"
                    size="sm"
                    className={agenceButtonStyles.outline}
                    onClick={onReset}
                >
                    Réinitialiser les filtres
                </Button>
            ) : null}
        </div>
    );
}

function StatCard({
    icon: Icon,
    label,
    value,
    accent = COLORS.blue,
    tint = '#eaf4fb',
}) {
    return (
        <Card className=" rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardContent className="mt-6 flex items-center gap-3 p-4">
                <span
                    className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                    style={{
                        backgroundColor: tint,
                        color: accent,
                    }}
                >
                    <Icon className="h-5 w-5" />
                </span>

                <div className="min-w-0">
                    <p className="text-xl font-bold text-[#0f172a]">
                        {value}
                    </p>

                    <p className="truncate text-[11px] uppercase tracking-wide text-[#94a3b8]">
                        {label}
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

export default function Index({
    personnel = [],
    stats = {},
    roles = [],
    filters = {},
}) {
    const [search, setSearch] = useState(filters.search ?? '');

    const [roleId, setRoleId] = useState(
        filters.role_id ? String(filters.role_id) : '__all__'
    );

    const [statut, setStatut] = useState(
        filters.statut ? String(filters.statut) : '__all__'
    );

    const didMountRef = useRef(false);

    const rows = useMemo(
        () => asArray(personnel?.data ?? personnel).map(normalizePersonnel),
        [personnel]
    );

    const roleOptions = useMemo(
        () => asArray(roles).map(normalizeRoleOption),
        [roles]
    );

    const selectedRoleLabel =
        roleOptions.find((item) => item.value === roleId)?.label ?? 'Tous les rôles';

    const selectedStatusLabel =
        statut === 'actif'
            ? 'Actifs'
            : statut === 'inactif'
                ? 'Inactifs'
                : statut === 'suspendu'
                    ? 'Suspendus'
                    : 'Tous les statuts';

    const activeFilters = [
        search.trim() ? `"${search.trim()}"` : null,
        roleId !== '__all__' ? selectedRoleLabel : null,
        statut !== '__all__' ? selectedStatusLabel : null,
    ].filter(Boolean);

    useEffect(() => {
        if (!didMountRef.current) {
            didMountRef.current = true;
            return undefined;
        }

        const timeoutId = window.setTimeout(() => {
            const query = {};
            const trimmedSearch = search.trim();

            if (trimmedSearch) {
                query.search = trimmedSearch;
            }

            if (roleId !== '__all__') {
                query.role_id = roleId;
            }

            if (statut !== '__all__') {
                query.statut = statut;
            }

            router.get('/agence/personnel', query, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [search, roleId, statut]);

    const resetFilters = () => {
        setSearch('');
        setRoleId('__all__');
        setStatut('__all__');

        router.get('/agence/personnel', {}, {
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

    const handleToggleStatus = useCallback((personnelItem) => {
        const action = personnelItem.is_active ? 'désactiver' : 'activer';

        const url = personnelItem.is_active
            ? `/agence/personnel/${personnelItem.id}/deactivate`
            : `/agence/personnel/${personnelItem.id}/activate`;

        if (!window.confirm(`Voulez-vous ${action} ${personnelItem.name} ?`)) {
            return;
        }

        router.patch(url, {}, {
            preserveScroll: true,
        });
    }, []);

    const kpis = [
        {
            label: 'Total personnel',
            value: number(stats.total ?? rows.length),
            icon: UserRound,
            accent: COLORS.blue,
            tint: '#eaf4fb',
        },
        {
            label: 'Personnel actif',
            value: number(stats.actifs ?? rows.filter((item) => item.is_active).length),
            icon: UserCheck,
            accent: COLORS.greenDark,
            tint: '#eef8df',
        },
        {
            label: 'Personnel inactif',
            value: number(stats.inactifs ?? rows.filter((item) => !item.is_active).length),
            icon: UserX,
            accent: COLORS.red,
            tint: '#fff4f4',
        },
        {
            label: 'Personnel suspendu',
            value: number(stats.suspendu ?? 0),
            icon: ShieldCheck,
            accent: COLORS.amber,
            tint: '#fffbeb',
        },
        {
            label: 'Avec accès',
            value: number(stats.avec_acces ?? rows.filter((item) => item.is_acces).length),
            icon: ShieldCheck,
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
                        title="Personnel"
                        sortDirection={column.getIsSorted()}
                        onSort={() =>
                            column.toggleSorting(column.getIsSorted() === 'asc')
                        }
                    />
                ),

                meta: {
                    label: 'Personnel',
                },

                cell: ({ row }) => {
                    const personnelItem = row.original;

                    return (
                        <div className="flex items-center gap-3">
                            <AvatarName name={personnelItem.name} />

                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-[#0f172a]">
                                    {personnelItem.name}
                                </p>

                                <p className="font-mono text-xs text-[#94a3b8]">
                                    {personnelItem.matricule}
                                </p>

                                {personnelItem.is_acces ? (
                                    <div className="mt-1">
                                        <Badge
                                            variant="outline"
                                            className="rounded-full border-[#c8d4de] text-[11px]"
                                        >
                                            <ShieldCheck className="mr-1 h-3 w-3" />
                                            Accès système
                                        </Badge>
                                    </div>
                                ) : null}
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
                        onSort={() =>
                            column.toggleSorting(column.getIsSorted() === 'asc')
                        }
                    />
                ),

                meta: {
                    label: 'Contact',
                },

                cell: ({ row }) => {
                    const personnelItem = row.original;

                    return (
                        <div className="space-y-1 text-sm text-[#0f172a]">
                            <div className="flex items-center gap-2">
                                <Phone className="h-4 w-4 shrink-0 text-[#5f7182]" />
                                <span>{personnelItem.tel1 || '—'}</span>
                            </div>

                            {personnelItem.tel2 ? (
                                <div className="flex items-center gap-2 text-[#5f7182]">
                                    <Phone className="h-4 w-4 shrink-0" />
                                    <span>{personnelItem.tel2}</span>
                                </div>
                            ) : null}

                            {personnelItem.email ? (
                                <div className="flex items-center gap-2 text-[#5f7182]">
                                    <Mail className="h-4 w-4 shrink-0" />
                                    <span className="truncate">
                                        {personnelItem.email}
                                    </span>
                                </div>
                            ) : null}
                        </div>
                    );
                },
            },

            {
                id: 'role',
                accessorFn: (row) => row.role,

                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Rôle"
                        sortDirection={column.getIsSorted()}
                        onSort={() =>
                            column.toggleSorting(column.getIsSorted() === 'asc')
                        }
                    />
                ),

                meta: {
                    label: 'Rôle',
                },

                cell: ({ row }) => {
                    const personnelItem = row.original;

                    return (
                        <div className="space-y-1">
                            <div className="flex items-center gap-2">
                                <BriefcaseBusiness className="h-4 w-4 shrink-0 text-[#00559b]" />

                                <p className="text-sm font-semibold text-[#0f172a]">
                                    {personnelItem.role}
                                </p>
                            </div>

                            <p className="text-xs text-[#5f7182]">
                                {personnelItem.fonction}
                            </p>
                        </div>
                    );
                },
            },

            {
                id: 'acces',
                header: 'Accès',

                meta: {
                    label: 'Accès',
                },

                cell: ({ row }) => {
                    const personnelItem = row.original;

                    return personnelItem.is_acces ? (
                        <Badge
                            variant="success"
                            className="rounded-full text-xs"
                        >
                            Autorisé
                        </Badge>
                    ) : (
                        <Badge
                            variant="outline"
                            className="rounded-full border-[#c8d4de] text-xs"
                        >
                            Aucun accès
                        </Badge>
                    );
                },
            },

            {
                id: 'statut',
                header: 'Statut',

                meta: {
                    label: 'Statut',
                },

                cell: ({ row }) => {
                    const personnelItem = row.original;

                    return personnelItem.is_active ? (
                        <Badge
                            variant="success"
                            className="rounded-full text-xs ring-1 ring-[#d8ebb7]"
                        >
                            Actif
                        </Badge>
                    ) : (
                        <Badge
                            variant="danger"
                            className="rounded-full text-xs"
                        >
                            Inactif
                        </Badge>
                    );
                },
            },

            {
                id: 'actions',

                header: () => (
                    <span className="block text-right">
                        Actions
                    </span>
                ),

                enableHiding: false,

                meta: {
                    label: 'Actions',
                    headerClassName: 'text-right',
                    cellClassName: 'text-right whitespace-nowrap',
                    disableExport: true,
                },

                cell: ({ row }) => {
                    const personnelItem = row.original;

                    return (
                        <div className="flex justify-end gap-1.5">
                            <Button
                                asChild
                                variant="outline"
                                size="icon"
                                className={agenceButtonStyles.actionBlueIcon}
                            >
                                <Link href={`/agence/personnel/${personnelItem.id}`}>
                                    <Eye className="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                asChild
                                variant="outline"
                                size="icon"
                                className={agenceButtonStyles.actionGreenIcon}
                            >
                                <Link href={`/agence/personnel/${personnelItem.id}/edit`}>
                                    <Pencil className="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                className={
                                    personnelItem.is_active
                                        ? agenceButtonStyles.actionRedIcon
                                        : agenceButtonStyles.actionGreenIcon
                                }
                                onClick={() => handleToggleStatus(personnelItem)}
                            >
                                {personnelItem.is_active ? (
                                    <UserX className="h-4 w-4" />
                                ) : (
                                    <UserCheck className="h-4 w-4" />
                                )}
                            </Button>
                        </div>
                    );
                },
            },
        ],
        [handleToggleStatus]
    );

    const pageLinks =
        Array.isArray(personnel?.links) && personnel.links.length > 3
            ? personnel
            : null;

    return (
        <AgenceLayout title="Personnel">
            <Head title="Personnel" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm text-[#5f7182]">
                            Gestion des employés et des accès à l'espace agence
                        </p>

                        <h2 className="text-2xl font-semibold text-[#0f172a]">
                            Gestion du personnel
                        </h2>
                    </div>

                    <Button
                        asChild
                        className={agenceButtonStyles.primary}
                    >
                        <Link href="/agence/personnel/create">
                            <Plus className="h-4 w-4" />
                            Nouveau personnel
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
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

                <DataTable
                    title="Liste du personnel"
                    columns={columns}
                    data={rows}
                    pagination={pageLinks}
                    onPageChange={handlePageChange}
                    searchValue={search}
                    onSearchChange={setSearch}
                    searchPlaceholder="Nom, matricule, téléphone, email..."
                    filtersSlot={
                        <div className="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
                            <Select
                                value={roleId}
                                onValueChange={setRoleId}
                            >
                                <SelectTrigger className="h-9 w-full rounded-xl md:w-[220px]">
                                    <SelectValue placeholder="Tous les rôles" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="__all__">
                                        Tous les rôles
                                    </SelectItem>

                                    {roleOptions.map((role) => (
                                        <SelectItem
                                            key={role.value}
                                            value={role.value}
                                        >
                                            {role.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select
                                value={statut}
                                onValueChange={setStatut}
                            >
                                <SelectTrigger className="h-9 w-full rounded-xl md:w-[170px]">
                                    <SelectValue placeholder="Tous les statuts" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="__all__">
                                        Tous les statuts
                                    </SelectItem>

                                    <SelectItem value="actif">
                                        Actifs
                                    </SelectItem>

                                    <SelectItem value="inactif">
                                        Inactifs
                                    </SelectItem>

                                    <SelectItem value="suspendu">
                                        Suspendus
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    }
                    onResetFilters={resetFilters}
                    emptyState={
                        <EmptyState
                            title="Aucun personnel trouvé"
                            desc={
                                activeFilters.length > 0
                                    ? 'Aucun membre du personnel ne correspond aux filtres appliqués.'
                                    : 'Aucun membre du personnel n’a encore été enregistré.'
                            }
                            onReset={
                                activeFilters.length > 0
                                    ? resetFilters
                                    : null
                            }
                        />
                    }
                />
            </div>
        </AgenceLayout>
    );
}
