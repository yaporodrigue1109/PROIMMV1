import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    Banknote,
    Building2,
    CalendarClock,
    DoorOpen,
    Plus,
    TrendingDown,
    TrendingUp,
    UserRound,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { cn } from '../../../lib/utils';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const statusTone = {
    paid: 'bg-[#eef8df] text-[#4d8500] ring-[#d8ebb7]',
    pending: 'bg-[#eaf4fb] text-[#00559b] ring-[#c8d4de]',
};

export default function Dashboard({
    stats = {},
    recentPayments = [],
    upcomingLeases = [],
    recentProperties = [],
}) {
    const {
        properties = 0,
        occupiedUnits = 0,
        vacantUnits = 0,
        tenants = 0,
        monthlyRevenue = 0,
        revenueTrend = 0,
        pendingPayments = 0,
        occupancyRate = 0,
    } = stats;

    const cards = [
        {
            label: 'Propriétés',
            value: number(properties),
            hint: `${number(occupiedUnits)} occupées · ${number(vacantUnits)} libres`,
            icon: Building2,
            accent: 'bg-[#eef8df] text-[#4d8500]',
        },
        {
            label: 'Locataires actifs',
            value: number(tenants),
            hint: 'Contrats en cours',
            icon: UserRound,
            accent: 'bg-[#eaf4fb] text-[#00559b]',
        },
        {
            label: 'Revenus du mois',
            value: currency(monthlyRevenue),
            hint: `${revenueTrend >= 0 ? '+' : ''}${revenueTrend}% vs mois dernier`,
            icon: Banknote,
            accent: 'bg-[#eaf4fb] text-[#00559b]',
            trend: revenueTrend,
        },
        {
            label: "Taux d'occupation",
            value: `${number(occupancyRate)}%`,
            hint: `${number(pendingPayments)} paiements en attente`,
            icon: DoorOpen,
            accent: 'bg-[#eef8df] text-[#4d8500]',
        },
    ];

    return (
        <AgenceLayout title="Tableau de bord">
            <Head title="Tableau de bord" />

            <div className="relative mx-auto flex w-full max-w-7xl flex-col gap-6 overflow-hidden">
                <div className="pointer-events-none absolute inset-x-0 top-0 -z-10 h-64 rounded-[32px] bg-gradient-to-br from-[#eef8df] via-white to-[#eaf4fb] blur-3xl" />
                {/* En-tête */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm text-[#5f7182]">Vue d&apos;ensemble</p>
                        <h2 className="text-2xl font-semibold text-[#0f172a]">
                            Bonjour, voici votre activité
                        </h2>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Button
                            asChild
                            variant="outline"
                            className={agenceButtonStyles.outline}
                        >
                            <Link href="/agence/statistiques">
                                Voir les statistiques
                                <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button asChild className={agenceButtonStyles.primary}>
                            <Link href="/agence/proprietes/create">
                                <Plus className="h-4 w-4" />
                                Ajouter une propriété
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Cartes KPI */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {cards.map((card) => {
                        const Icon = card.icon;
                        const TrendIcon = (card.trend ?? 0) >= 0 ? TrendingUp : TrendingDown;

                        return (
                            <Card key={card.label} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardDescription className="text-sm font-medium text-[#5f7182]">
                                        {card.label}
                                    </CardDescription>
                                    <span
                                        className={cn(
                                            'flex h-10 w-10 items-center justify-center rounded-xl',
                                            card.accent
                                        )}
                                    >
                                        <Icon className="h-5 w-5" />
                                    </span>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-2xl font-semibold text-[#0f172a]">{card.value}</p>
                                    <p className="mt-1 flex items-center gap-1 text-xs text-[#5f7182]">
                                        {card.trend !== undefined ? (
                                            <TrendIcon
                                                className={cn(
                                                    'h-3.5 w-3.5',
                                                    card.trend >= 0 ? 'text-[#76c206]' : 'text-[#00559b]'
                                                )}
                                            />
                                        ) : null}
                                        {card.hint}
                                    </p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Paiements récents */}
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle className="text-base text-[#0f172a]">Paiements récents</CardTitle>
                                <CardDescription className="text-[#5f7182]">
                                    Derniers encaissements enregistrés
                                </CardDescription>
                            </div>
                            <Button asChild variant="ghost" size="sm" className={agenceButtonStyles.subtle}>
                                <Link href="/agence/caisse">Tout voir</Link>
                            </Button>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="divide-y divide-[#eef3f7]">
                                {recentPayments.length === 0 ? (
                                    <p className="px-6 py-8 text-center text-sm text-[#5f7182]">
                                        Aucun paiement récent.
                                    </p>
                                ) : (
                                    recentPayments.map((payment) => (
                                        <div
                                            key={payment.id}
                                            className="flex items-center justify-between gap-4 px-6 py-4"
                                        >
                                            <div className="flex min-w-0 items-center gap-3">
                                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#eaf4fb] text-sm font-semibold text-[#00559b] ring-1 ring-[#c8d4de]">
                                                    {String(payment.tenant ?? 'L')
                                                        .slice(0, 1)
                                                        .toUpperCase()}
                                                </span>
                                                <div className="min-w-0">
                                                    <p className="truncate text-sm font-medium text-[#0f172a]">
                                                        {payment.tenant}
                                                    </p>
                                                    <p className="truncate text-xs text-[#5f7182]">
                                                        {payment.property} · {payment.date}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <span className="text-sm font-semibold text-[#0f172a]">
                                                    {currency(payment.amount)}
                                                </span>
                                                <Badge
                                                    variant="secondary"
                                                    className={cn(
                                                        'rounded-full text-xs ring-1',
                                                        payment.status === 'payé'
                                                            ? statusTone.paid
                                                            : statusTone.pending
                                                    )}
                                                >
                                                    {payment.status}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Échéances à venir */}
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base text-[#0f172a]">
                                <CalendarClock className="h-4 w-4 text-[#00559b]" />
                                Échéances à venir
                            </CardTitle>
                            <CardDescription className="text-[#5f7182]">Contrats à renouveler</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {upcomingLeases.length === 0 ? (
                                <p className="py-6 text-center text-sm text-[#5f7182]">
                                    Aucune échéance proche.
                                </p>
                            ) : (
                                upcomingLeases.map((lease) => (
                                    <div
                                        key={lease.id}
                                        className="rounded-xl border border-[#eef3f7] bg-[#f7fbfe] p-3"
                                    >
                                        <p className="truncate text-sm font-medium text-[#0f172a]">
                                            {lease.tenant}
                                        </p>
                                        <p className="truncate text-xs text-[#5f7182]">{lease.property}</p>
                                        <Separator className="my-2 bg-[#c8d4de]" />
                                        <div className="flex items-center justify-between text-xs">
                                            <span className="text-[#5f7182]">Fin de bail</span>
                                            <span className="font-medium text-[#00559b]">{lease.endDate}</span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Dernières propriétés */}
                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-base text-[#0f172a]">Dernières propriétés</CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Ajoutées récemment à votre portefeuille
                            </CardDescription>
                        </div>
                        <Button asChild variant="ghost" size="sm" className={agenceButtonStyles.subtle}>
                            <Link href="/agence/proprietes">Tout voir</Link>
                        </Button>
                    </CardHeader>
                    <CardContent className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        {recentProperties.length === 0 ? (
                            <p className="col-span-full py-8 text-center text-sm text-[#5f7182]">
                                Aucune propriété enregistrée.
                            </p>
                        ) : (
                            recentProperties.map((property) => (
                                <Link
                                    key={property.id}
                                    href={`/agence/proprietes/${property.id}`}
                                    className="group rounded-xl border border-[#c8d4de] bg-white p-4 transition hover:border-[#00559b] hover:shadow-md hover:shadow-[#00559b]/5"
                                >
                                    <div className="flex items-center justify-between">
                                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b] ring-1 ring-[#c8d4de]">
                                            <Building2 className="h-5 w-5" />
                                        </span>
                                        <Badge
                                            variant="secondary"
                                            className={cn(
                                                'rounded-full text-xs ring-1',
                                                property.status === 'Occupé'
                                                    ? statusTone.paid
                                                    : statusTone.pending
                                            )}
                                        >
                                            {property.status}
                                        </Badge>
                                    </div>
                                    <p className="mt-3 truncate text-sm font-semibold text-[#0f172a] group-hover:text-[#00559b]">
                                        {property.name}
                                    </p>
                                    <p className="truncate text-xs text-[#5f7182]">{property.location}</p>
                                    <p className="mt-2 text-sm font-medium text-[#0f172a]">
                                        {currency(property.rent)}
                                        <span className="text-xs font-normal text-[#5f7182]"> /mois</span>
                                    </p>
                                </Link>
                            ))
                        )}
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
