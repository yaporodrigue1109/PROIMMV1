import { useEffect, useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import {
    CalendarClock,
    CreditCard,
    Search,
    Ticket,
} from 'lucide-react';

import AdminLayout from '../../../Layouts/AdminLayout';

import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { ScrollArea } from '../../../components/ui/scroll-area';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';

const statusLabel = {
    Actif: 'Actif',
    'En attente': 'En attente',
    Expire: 'Expiré',
};

const statusVariant = {
    Actif: 'default',
    'En attente': 'secondary',
    Expire: 'destructive',
};

const paymentLabel = {
    Paye: 'Payé',
    'A confirmer': 'À confirmer',
};

const paymentVariant = {
    Paye: 'default',
    'A confirmer': 'secondary',
};

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0)) + ' FCFA';

const formatDate = (value) => {
    if (!value) return 'Non défini';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('fr-FR');
};

const initials = (name) => {
    const parts = String(name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    return (
        parts
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase())
            .join('') || 'AB'
    );
};

const daysRemaining = (value) => {
    if (!value) return null;

    const end = new Date(value);

    if (Number.isNaN(end.getTime())) {
        return null;
    }

    return Math.ceil((end.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
};

export default function Index({
    abonnements = [],
    plans = [],
    stats = {},
    nextRenewals = [],
}) {
    const items = abonnements?.data ?? abonnements ?? [];

    const [query, setQuery] = useState('');
    const [status, setStatus] = useState('tous');
    const [selectedCode, setSelectedCode] = useState(items[0]?.code_agence ?? '');

    const filteredItems = useMemo(() => {
        return items.filter((abonnement) => {
            const searchText = [
                abonnement.agence,
                abonnement.code_agence,
                abonnement.plan,
                abonnement.cycle,
                abonnement.notes,
                ...(abonnement.modules ?? []),
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            const matchesSearch =
                !query || searchText.includes(query.toLowerCase());

            const matchesStatus =
                status === 'tous' || abonnement.statut === status;

            return matchesSearch && matchesStatus;
        });
    }, [items, query, status]);

    useEffect(() => {
        if (!filteredItems.length) {
            setSelectedCode('');
            return;
        }

        const stillVisible = filteredItems.some(
            (item) => item.code_agence === selectedCode
        );

        if (!stillVisible) {
            setSelectedCode(filteredItems[0].code_agence);
        }
    }, [filteredItems, selectedCode]);

    const selectedItem =
        filteredItems.find((item) => item.code_agence === selectedCode) ??
        filteredItems[0] ??
        null;

    const total = stats.total ?? items.length;

    const actifs =
        stats.actifs ??
        items.filter((item) => item.statut === 'Actif').length;

    const attente =
        stats.attente ??
        items.filter((item) => item.statut === 'En attente').length;

    const expires =
        stats.expires ??
        items.filter((item) => item.statut === 'Expire').length;

    const revenu =
        stats.revenu ??
        items
            .filter((item) => item.statut === 'Actif')
            .reduce((sum, item) => sum + Number(item.montant ?? 0), 0);

    const paiementsPayes = items.filter((item) => item.paiement === 'Paye').length;

    const paiementsAConfirmer = items.filter(
        (item) => item.paiement === 'A confirmer'
    ).length;

    return (
        <AdminLayout title="Abonnements">
            <section className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="max-w-2xl">
                            <h1 className="mt-4 text-3xl font-semibold tracking-tight text-[#0f172a] md:text-4xl">
                                Gestion des abonnements
                            </h1>

                        
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button
                                asChild
                                className=" mt-4 h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]"
                            >
                                <Link href="/admin/abonnements/create">
                                    <Ticket className="h-4 w-4" />
                                    Abonner une agence
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Total abonnements"
                        value={total}
                        color="text-slate-600"
                    />

                    <StatCard
                        label="Actifs"
                        value={actifs}
                        color="text-emerald-600"
                    />

                    <StatCard
                        label="En attente"
                        value={attente}
                        color="text-amber-600"
                    />

                    <StatCard
                        label="Expirés"
                        value={expires}
                        color="text-red-600"
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[360px_1fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="space-y-4 border-b border-slate-200">
                            <CardTitle className="text-base">
                                Abonnements{' '}
                                <span className="text-slate-400">
                                    {total}
                                </span>
                            </CardTitle>

                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />

                                <Input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Rechercher un abonnement…"
                                    className="h-11 rounded-2xl bg-slate-50 pl-10"
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                {[
                                    ['tous', 'Tous'],
                                    ['Actif', 'Actifs'],
                                    ['En attente', 'En attente'],
                                    ['Expire', 'Expirés'],
                                ].map(([key, label]) => (
                                    <Button
                                        key={key}
                                        type="button"
                                        size="sm"
                                        variant={status === key ? 'default' : 'outline'}
                                        onClick={() => setStatus(key)}
                                        className="rounded-full"
                                    >
                                        {label}
                                    </Button>
                                ))}
                            </div>
                        </CardHeader>

                        <CardContent className="p-3">
                            <ScrollArea className="h-[650px]">
                                <div className="space-y-2 pr-3">
                                    {filteredItems.map((abonnement) => {
                                        const isSelected =
                                            selectedItem?.code_agence ===
                                            abonnement.code_agence;

                                        return (
                                            <Button
                                                key={abonnement.code_agence}
                                                type="button"
                                                variant="ghost"
                                                onClick={() =>
                                                    setSelectedCode(
                                                        abonnement.code_agence
                                                    )
                                                }
                                                className={`mt-4 h-auto w-full justify-start rounded-2xl border p-4 text-left ${
                                                    isSelected
                                                        ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                        : 'border-slate-200 bg-white hover:bg-slate-50'
                                                }`}
                                            >
                                                <div className="w-full space-y-3">
                                                    <div className="flex items-center justify-between gap-3">
                                                        <span className="text-xs font-semibold text-slate-500">
                                                            {abonnement.code_agence ?? 'N/A'}
                                                        </span>

                                                        <Badge
                                                            variant={
                                                                statusVariant[
                                                                    abonnement.statut
                                                                ] ?? 'secondary'
                                                            }
                                                        >
                                                            {statusLabel[
                                                                abonnement.statut
                                                            ] ?? abonnement.statut}
                                                        </Badge>
                                                    </div>

                                                    <div className="flex items-center gap-3">
                                                        <Avatar>
                                                            <AvatarFallback className="bg-slate-900 text-white">
                                                                {initials(
                                                                    abonnement.agence
                                                                )}
                                                            </AvatarFallback>
                                                        </Avatar>

                                                        <div className="min-w-0">
                                                            <p className="truncate text-sm font-semibold text-slate-900">
                                                                {abonnement.agence ??
                                                                    'Agence sans nom'}
                                                            </p>

                                                            <p className="truncate text-xs text-slate-500">
                                                                {abonnement.plan ??
                                                                    'Aucun plan'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </Button>
                                        );
                                    })}

                                    {filteredItems.length === 0 && (
                                        <div className="p-6 text-center text-sm text-slate-500">
                                            Aucun abonnement ne correspond à la recherche.
                                        </div>
                                    )}
                                </div>
                            </ScrollArea>
                        </CardContent>
                    </Card>

                    <Card className="min-h-[calc(100vh-260px)] rounded-3xl border-slate-200 shadow-sm">
                        {selectedItem ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <CardDescription className="text-xs font-semibold uppercase tracking-[0.2em]">
                                            {selectedItem.code_agence}
                                        </CardDescription>

                                        <CardTitle className="mt-2 text-2xl">
                                            {selectedItem.agence ?? 'Agence sans nom'}
                                        </CardTitle>
                                    </div>

                                    <div className="flex gap-2">
                                        {selectedItem.statut === 'Expire' ? (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="rounded-xl border-rose-200 text-rose-700 hover:bg-rose-50"
                                                onClick={() => {
                                                    if (!selectedItem.code_agence) return;

                                                    router.get(
                                                        `/admin/abonnements/${selectedItem.code_agence}/edit?renew=1`
                                                    );
                                                }}
                                            >
                                                Renouveler
                                            </Button>
                                        ) : null}

                                        <Button
                                            type="button"
                                            onClick={() => {
                                                if (!selectedItem.code_agence) return;

                                                router.get(
                                                    `/admin/abonnements/${selectedItem.code_agence}/edit?renew=1`
                                                );
                                            }}
                                            className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                                        >
                                            Modifier
                                        </Button>
                                    </div>
                                </CardHeader>

                                <Tabs defaultValue="apercu" className="w-full">
                                    <div className="mb-4 px-6 pt-5">
                                        <TabsList>
                                            <TabsTrigger value="apercu">
                                                Aperçu
                                            </TabsTrigger>
                                            <TabsTrigger value="modules">
                                                Modules
                                            </TabsTrigger>
                                            <TabsTrigger value="historique">
                                                Historique
                                            </TabsTrigger>
                                        </TabsList>
                                    </div>

                                    <div className="mb-4 grid gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4 md:grid-cols-5">
                                        <MetaItem label="Statut">
                                            <Badge
                                                variant={
                                                    statusVariant[
                                                        selectedItem.statut
                                                    ] ?? 'secondary'
                                                }
                                            >
                                                {statusLabel[selectedItem.statut] ??
                                                    selectedItem.statut}
                                            </Badge>
                                        </MetaItem>

                                        <MetaItem
                                            label="Plan"
                                            value={selectedItem.plan ?? 'Aucun plan'}
                                        />

                                        <MetaItem
                                            label="Cycle"
                                            value={selectedItem.cycle ?? 'Non défini'}
                                        />

                                        <MetaItem
                                            label="Paiement"
                                        >
                                            <Badge
                                                variant={
                                                    paymentVariant[
                                                        selectedItem.paiement
                                                    ] ?? 'secondary'
                                                }
                                            >
                                                {paymentLabel[
                                                    selectedItem.paiement
                                                ] ?? selectedItem.paiement ?? 'Non défini'}
                                            </Badge>
                                        </MetaItem>

                                        <MetaItem
                                            label="Montant"
                                            value={formatMoney(selectedItem.montant)}
                                        />
                                    </div>

                                    <TabsContent value="apercu" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <Card className="rounded-2xl border-slate-200">
                                                <CardContent className="mt-4 flex gap-4 p-5">
                                                    <Avatar className="h-16 w-16">
                                                        <AvatarFallback className="bg-slate-900 text-lg font-bold text-white">
                                                            {initials(
                                                                selectedItem.agence
                                                            )}
                                                        </AvatarFallback>
                                                    </Avatar>

                                                    <div className="grid flex-1 gap-3 md:grid-cols-2">
                                                        <Info
                                                            label="Agence"
                                                            value={selectedItem.agence}
                                                        />

                                                        <Info
                                                            label="Code agence"
                                                            value={selectedItem.code_agence}
                                                        />

                                                        <Info
                                                            label="Plan souscrit"
                                                            value={selectedItem.plan}
                                                        />

                                                        <Info
                                                            label="Cycle de facturation"
                                                            value={selectedItem.cycle}
                                                        />
                                                    </div>
                                                </CardContent>
                                            </Card>

                                            <Section title="Période d'abonnement">
                                                <div className="grid gap-4 md:grid-cols-3">
                                                    <Info
                                                        label="Date de début"
                                                        value={formatDate(
                                                            selectedItem.date_debut
                                                        )}
                                                    />

                                                    <Info
                                                        label="Date de fin"
                                                        value={formatDate(
                                                            selectedItem.date_fin
                                                        )}
                                                    />

                                                    <Info
                                                        label="Jours restants"
                                                        value={
                                                            daysRemaining(
                                                                selectedItem.date_fin
                                                            ) > 0
                                                                ? `${daysRemaining(
                                                                      selectedItem.date_fin
                                                                  )} jour(s)`
                                                                : 'Expiré'
                                                        }
                                                    />
                                                </div>
                                            </Section>

                                            <Section title="Paiement">
                                                <div className="grid gap-4 md:grid-cols-3">
                                                    <Info
                                                        label="Montant"
                                                        value={formatMoney(
                                                            selectedItem.montant
                                                        )}
                                                    />

                                                    <Info
                                                        label="Statut du paiement"
                                                        value={
                                                            paymentLabel[
                                                                selectedItem.paiement
                                                            ] ??
                                                            selectedItem.paiement ??
                                                            'Non défini'
                                                        }
                                                    />

                                                    <Info
                                                        label="Paiements confirmés"
                                                        value={paiementsPayes}
                                                    />
                                                </div>
                                            </Section>

                                            <Section title="Notes">
                                                <p className="text-sm leading-6 text-slate-700">
                                                    {selectedItem.notes ??
                                                        'Aucune note disponible pour cet abonnement.'}
                                                </p>
                                            </Section>
                                        </CardContent>
                                    </TabsContent>

                                    <TabsContent value="modules" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <Section title="Modules actifs">
                                                <div className="flex flex-wrap gap-2">
                                                    {(selectedItem.modules ?? []).length >
                                                    0 ? (
                                                        selectedItem.modules.map(
                                                            (module) => (
                                                                <Badge
                                                                    key={module}
                                                                    variant="outline"
                                                                    className="rounded-full"
                                                                >
                                                                    {module}
                                                                </Badge>
                                                            )
                                                        )
                                                    ) : (
                                                        <Badge
                                                            variant="outline"
                                                            className="rounded-full"
                                                        >
                                                            Aucun module actif
                                                        </Badge>
                                                    )}
                                                </div>
                                            </Section>

                                            <div className="grid gap-4 md:grid-cols-4">
                                                <MiniStat
                                                    label="Plans disponibles"
                                                    value={plans.length ?? 0}
                                                />

                                                <MiniStat
                                                    label="Modules actifs"
                                                    value={
                                                        selectedItem.modules?.length ??
                                                        0
                                                    }
                                                />

                                                <MiniStat
                                                    label="Échéances proches"
                                                    value={nextRenewals.length ?? 0}
                                                />

                                                <MiniStat
                                                    label="À confirmer"
                                                    value={paiementsAConfirmer}
                                                />
                                            </div>
                                        </CardContent>
                                    </TabsContent>

                                    <TabsContent value="historique" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <div className="overflow-hidden rounded-2xl border border-slate-200">
                                                <table className="w-full text-sm">
                                                    <thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                                        <tr>
                                                            <th className="px-4 py-3 font-medium">
                                                                Période
                                                            </th>
                                                            <th className="px-4 py-3 font-medium">
                                                                Montant
                                                            </th>
                                                            <th className="px-4 py-3 font-medium">
                                                                Paiement
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody className="divide-y divide-slate-200">
                                                        {selectedItem.history?.length ? (
                                                            selectedItem.history.map(
                                                                (entry) => (
                                                                    <tr
                                                                        key={`${entry.periode}-${entry.montant}-${entry.statut}`}
                                                                    >
                                                                        <td className="px-4 py-3 text-slate-900">
                                                                            {
                                                                                entry.periode
                                                                            }
                                                                        </td>

                                                                        <td className="px-4 py-3 text-slate-900">
                                                                            {formatMoney(
                                                                                entry.montant
                                                                            )}
                                                                        </td>

                                                                        <td className="px-4 py-3">
                                                                            <Badge
                                                                                variant={
                                                                                    paymentVariant[
                                                                                        entry
                                                                                            .statut
                                                                                    ] ??
                                                                                    'secondary'
                                                                                }
                                                                            >
                                                                                {paymentLabel[
                                                                                    entry
                                                                                        .statut
                                                                                ] ??
                                                                                    entry.statut}
                                                                            </Badge>
                                                                        </td>
                                                                    </tr>
                                                                )
                                                            )
                                                        ) : (
                                                            <tr>
                                                                <td
                                                                    colSpan="3"
                                                                    className="px-4 py-8 text-center text-slate-500"
                                                                >
                                                                    Aucun historique
                                                                    disponible.
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </CardContent>
                                    </TabsContent>
                                </Tabs>
                            </>
                        ) : (
                            <CardContent className="flex min-h-[calc(100vh-260px)] items-center justify-center">
                                <div className="text-center">
                                    <h3 className="text-xl font-semibold text-slate-900">
                                        Aucun abonnement à afficher
                                    </h3>

                                    <p className="mt-2 text-sm text-slate-500">
                                        Créez un abonnement pour voir sa fiche ici.
                                    </p>
                                </div>
                            </CardContent>
                        )}
                    </Card>
                </div>
            </section>
        </AdminLayout>
    );
}

function StatCard({ label, value, color = 'text-slate-500' }) {
    return (
        <Card className="rounded-2xl border border-slate-200 shadow-sm">
            <CardContent className="flex min-h-[140px] flex-col justify-center px-8 py-7">
                <p className={`text-sm font-semibold ${color}`}>{label}</p>

                <p className="mt-2 text-5xl font-bold tracking-tight text-slate-900">
                    {value}
                </p>
            </CardContent>
        </Card>
    );
}

function Section({ title, children }) {
    return (
        <Card className="rounded-2xl border-slate-200">
            <CardHeader>
                <CardTitle className="text-sm uppercase tracking-[0.18em] text-slate-500">
                    {title}
                </CardTitle>
            </CardHeader>

            <CardContent>{children}</CardContent>
        </Card>
    );
}

function Info({ label, value, full = false }) {
    return (
        <div className={full ? 'md:col-span-2' : ''}>
            <p className="text-xs text-slate-500">{label}</p>

            <p className="mt-1 text-sm font-semibold text-slate-900">
                {value || 'Non spécifié'}
            </p>
        </div>
    );
}

function MetaItem({ label, value, children }) {
    return (
        <div>
            <p className="text-xs text-slate-500">{label}</p>

            <div className="mt-1 text-sm font-semibold text-slate-900">
                {children ?? value}
            </div>
        </div>
    );
}

function MiniStat({ label, value }) {
    return (
        <Card className="rounded-2xl border-slate-200 shadow-sm">
            <CardContent className="mt-4 p-5">
                <p className="text-sm text-slate-500">{label}</p>

                <p className="mt-2 text-xl font-semibold text-slate-900">
                    {value}
                </p>
            </CardContent>
        </Card>
    );
}
