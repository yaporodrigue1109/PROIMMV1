import { useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import {
    Banknote,
    Building2,
    DoorOpen,
    TrendingDown,
    TrendingUp,
    UserRound,
    Wallet,
} from 'lucide-react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
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
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

/* Palette alignée sur le reste de l'espace agence */
const COLORS = {
    blue: '#00559b',
    blueSoft: '#eaf4fb',
    green: '#76c206',
    greenDark: '#4d8500',
    greenSoft: '#eef8df',
    slate: '#5f7182',
    border: '#c8d4de',
    ink: '#0f172a',
};

const PIE_PALETTE = [COLORS.blue, COLORS.green, '#f2a900', '#b42318', '#7c8db0'];

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const compactCurrency = (value) => {
    const n = Number(value ?? 0);
    if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`;
    if (n >= 1_000) return `${Math.round(n / 1_000)}k`;
    return String(n);
};

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

/* Données de démonstration (remplacées par les props envoyées depuis le contrôleur) */
const DEMO_REVENUE = [
    { month: 'Jan', revenus: 4200000, depenses: 1200000 },
    { month: 'Fév', revenus: 4550000, depenses: 1350000 },
    { month: 'Mar', revenus: 4380000, depenses: 1100000 },
    { month: 'Avr', revenus: 4900000, depenses: 1500000 },
    { month: 'Mai', revenus: 5200000, depenses: 1420000 },
    { month: 'Juin', revenus: 5650000, depenses: 1600000 },
    { month: 'Juil', revenus: 5400000, depenses: 1380000 },
    { month: 'Août', revenus: 5980000, depenses: 1700000 },
    { month: 'Sep', revenus: 6150000, depenses: 1550000 },
    { month: 'Oct', revenus: 6420000, depenses: 1620000 },
    { month: 'Nov', revenus: 6100000, depenses: 1490000 },
    { month: 'Déc', revenus: 6800000, depenses: 1750000 },
];

const DEMO_OCCUPANCY = [
    { month: 'Jan', taux: 78 },
    { month: 'Fév', taux: 81 },
    { month: 'Mar', taux: 80 },
    { month: 'Avr', taux: 84 },
    { month: 'Mai', taux: 86 },
    { month: 'Juin', taux: 88 },
    { month: 'Juil', taux: 87 },
    { month: 'Août', taux: 90 },
    { month: 'Sep', taux: 91 },
    { month: 'Oct', taux: 92 },
    { month: 'Nov', taux: 90 },
    { month: 'Déc', taux: 93 },
];

const DEMO_PAYMENT_STATUS = [
    { name: 'Payé', value: 68 },
    { name: 'En attente', value: 22 },
    { name: 'En retard', value: 10 },
];

const DEMO_PROPERTY_TYPES = [
    { type: 'Appartement', total: 42 },
    { type: 'Villa', total: 18 },
    { type: 'Studio', total: 27 },
    { type: 'Bureau', total: 12 },
    { type: 'Local', total: 9 },
];

/* Tooltip personnalisé pour rester cohérent avec le thème */
function ChartTooltip({ active, payload, label, formatter }) {
    if (!active || !payload?.length) return null;

    return (
        <div className="rounded-xl border border-[#c8d4de] bg-white px-3 py-2 shadow-lg">
            {label ? (
                <p className="mb-1 text-xs font-semibold text-[#0f172a]">{label}</p>
            ) : null}
            <div className="flex flex-col gap-1">
                {payload.map((entry) => (
                    <div key={entry.dataKey ?? entry.name} className="flex items-center gap-2 text-xs">
                        <span
                            className="h-2.5 w-2.5 rounded-full"
                            style={{ backgroundColor: entry.color ?? entry.payload?.fill }}
                        />
                        <span className="text-[#5f7182]">{entry.name}</span>
                        <span className="font-semibold text-[#0f172a]">
                            {formatter ? formatter(entry.value) : entry.value}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default function Statistiques({
    stats = {},
    revenueSeries = DEMO_REVENUE,
    occupancySeries = DEMO_OCCUPANCY,
    paymentStatus = DEMO_PAYMENT_STATUS,
    propertyTypes = DEMO_PROPERTY_TYPES,
}) {
    const [period, setPeriod] = useState('12');

    const {
        totalRevenue = 62800000,
        revenueTrend = 12.4,
        collectedRate = 88,
        collectedTrend = 3.2,
        averageRent = 185000,
        rentTrend = 4.1,
        occupancyRate = 91,
        occupancyTrend = 2.5,
    } = stats;

    const revenueData = useMemo(() => {
        const count = period === '6' ? 6 : period === '3' ? 3 : 12;
        return revenueSeries.slice(-count);
    }, [revenueSeries, period]);

    const occupancyData = useMemo(() => {
        const count = period === '6' ? 6 : period === '3' ? 3 : 12;
        return occupancySeries.slice(-count);
    }, [occupancySeries, period]);

    const kpis = [
        {
            label: 'Revenus cumulés',
            value: currency(totalRevenue),
            trend: revenueTrend,
            icon: Banknote,
            accent: 'bg-[#eef8df] text-[#4d8500]',
        },
        {
            label: 'Taux de recouvrement',
            value: `${number(collectedRate)}%`,
            trend: collectedTrend,
            icon: Wallet,
            accent: 'bg-[#eaf4fb] text-[#00559b]',
        },
        {
            label: 'Loyer moyen',
            value: currency(averageRent),
            trend: rentTrend,
            icon: Building2,
            accent: 'bg-[#eaf4fb] text-[#00559b]',
        },
        {
            label: "Taux d'occupation",
            value: `${number(occupancyRate)}%`,
            trend: occupancyTrend,
            icon: DoorOpen,
            accent: 'bg-[#eef8df] text-[#4d8500]',
        },
    ];

    const periods = [
        { value: '3', label: '3 mois' },
        { value: '6', label: '6 mois' },
        { value: '12', label: '12 mois' },
    ];

    return (
        <AgenceLayout title="Statistiques">
            <Head title="Statistiques" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                {/* En-tête */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm text-[#5f7182]">Analyse & performance</p>
                        <h2 className="text-2xl font-semibold text-[#0f172a]">
                            Statistiques de l&apos;agence
                        </h2>
                    </div>

                    <div className="flex items-center gap-1 rounded-xl border border-[#c8d4de] bg-white p-1 shadow-sm">
                        {periods.map((item) => (
                            <Button
                                key={item.value}
                                type="button"
                                size="sm"
                                variant={period === item.value ? 'default' : 'ghost'}
                                className={cn(
                                    'px-3 text-sm',
                                    period === item.value
                                        ? agenceButtonStyles.tabActive
                                        : agenceButtonStyles.tabInactive
                                )}
                                onClick={() => setPeriod(item.value)}
                            >
                                {item.label}
                            </Button>
                        ))}
                    </div>
                </div>

                {/* KPI */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {kpis.map((kpi) => {
                        const Icon = kpi.icon;
                        const positive = (kpi.trend ?? 0) >= 0;
                        const TrendIcon = positive ? TrendingUp : TrendingDown;

                        return (
                            <Card key={kpi.label} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardDescription className="text-sm font-medium text-[#5f7182]">
                                        {kpi.label}
                                    </CardDescription>
                                    <span
                                        className={cn(
                                            'flex h-10 w-10 items-center justify-center rounded-xl',
                                            kpi.accent
                                        )}
                                    >
                                        <Icon className="h-5 w-5" />
                                    </span>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-2xl font-semibold text-[#0f172a]">{kpi.value}</p>
                                    <p className="mt-1 flex items-center gap-1 text-xs">
                                        <TrendIcon
                                            className={cn(
                                                'h-3.5 w-3.5',
                                                positive ? 'text-[#76c206]' : 'text-[#b42318]'
                                            )}
                                        />
                                        <span
                                            className={cn(
                                                'font-medium',
                                                positive ? 'text-[#4d8500]' : 'text-[#b42318]'
                                            )}
                                        >
                                            {positive ? '+' : ''}
                                            {kpi.trend}%
                                        </span>
                                        <span className="text-[#5f7182]">vs période préc.</span>
                                    </p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Revenus vs dépenses */}
                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-base text-[#0f172a]">
                                Revenus & dépenses
                            </CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Évolution mensuelle des encaissements et des charges
                            </CardDescription>
                        </div>
                        <Badge
                            variant="secondary"
                            className="rounded-full bg-[#eef8df] text-[#4d8500] ring-1 ring-[#d8ebb7]"
                        >
                            {revenueTrend >= 0 ? '+' : ''}
                            {revenueTrend}%
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[320px] w-full">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={revenueData} margin={{ top: 10, right: 8, left: -8, bottom: 0 }}>
                                    <defs>
                                        <linearGradient id="fillRevenus" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor={COLORS.blue} stopOpacity={0.25} />
                                            <stop offset="95%" stopColor={COLORS.blue} stopOpacity={0} />
                                        </linearGradient>
                                        <linearGradient id="fillDepenses" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor={COLORS.green} stopOpacity={0.25} />
                                            <stop offset="95%" stopColor={COLORS.green} stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid strokeDasharray="3 3" stroke="#eef3f7" vertical={false} />
                                    <XAxis
                                        dataKey="month"
                                        tickLine={false}
                                        axisLine={false}
                                        tick={{ fill: COLORS.slate, fontSize: 12 }}
                                    />
                                    <YAxis
                                        tickLine={false}
                                        axisLine={false}
                                        width={48}
                                        tick={{ fill: COLORS.slate, fontSize: 12 }}
                                        tickFormatter={compactCurrency}
                                    />
                                    <Tooltip content={<ChartTooltip formatter={currency} />} />
                                    <Legend
                                        iconType="circle"
                                        wrapperStyle={{ fontSize: 12, color: COLORS.slate }}
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="revenus"
                                        name="Revenus"
                                        stroke={COLORS.blue}
                                        strokeWidth={2}
                                        fill="url(#fillRevenus)"
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="depenses"
                                        name="Dépenses"
                                        stroke={COLORS.green}
                                        strokeWidth={2}
                                        fill="url(#fillDepenses)"
                                    />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Taux d'occupation */}
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base text-[#0f172a]">
                                Taux d&apos;occupation
                            </CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Pourcentage de biens loués par mois
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[280px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <LineChart
                                        data={occupancyData}
                                        margin={{ top: 10, right: 8, left: -16, bottom: 0 }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" stroke="#eef3f7" vertical={false} />
                                        <XAxis
                                            dataKey="month"
                                            tickLine={false}
                                            axisLine={false}
                                            tick={{ fill: COLORS.slate, fontSize: 12 }}
                                        />
                                        <YAxis
                                            domain={[0, 100]}
                                            tickLine={false}
                                            axisLine={false}
                                            width={40}
                                            tick={{ fill: COLORS.slate, fontSize: 12 }}
                                            tickFormatter={(v) => `${v}%`}
                                        />
                                        <Tooltip content={<ChartTooltip formatter={(v) => `${v}%`} />} />
                                        <Line
                                            type="monotone"
                                            dataKey="taux"
                                            name="Occupation"
                                            stroke={COLORS.blue}
                                            strokeWidth={2.5}
                                            dot={{ r: 3, fill: COLORS.blue }}
                                            activeDot={{ r: 5 }}
                                        />
                                    </LineChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Statut des paiements */}
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base text-[#0f172a]">Statut des paiements</CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Répartition des loyers du mois
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[220px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={paymentStatus}
                                            dataKey="value"
                                            nameKey="name"
                                            cx="50%"
                                            cy="50%"
                                            innerRadius={55}
                                            outerRadius={85}
                                            paddingAngle={2}
                                            stroke="none"
                                        >
                                            {paymentStatus.map((entry, index) => (
                                                <Cell
                                                    key={entry.name}
                                                    fill={PIE_PALETTE[index % PIE_PALETTE.length]}
                                                />
                                            ))}
                                        </Pie>
                                        <Tooltip content={<ChartTooltip formatter={(v) => `${v}%`} />} />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                            <div className="mt-2 flex flex-col gap-2">
                                {paymentStatus.map((entry, index) => (
                                    <div
                                        key={entry.name}
                                        className="flex items-center justify-between text-sm"
                                    >
                                        <span className="flex items-center gap-2 text-[#5f7182]">
                                            <span
                                                className="h-2.5 w-2.5 rounded-full"
                                                style={{
                                                    backgroundColor:
                                                        PIE_PALETTE[index % PIE_PALETTE.length],
                                                }}
                                            />
                                            {entry.name}
                                        </span>
                                        <span className="font-semibold text-[#0f172a]">
                                            {entry.value}%
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Répartition des biens par type */}
                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-base text-[#0f172a]">
                                Répartition du portefeuille
                            </CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Nombre de biens par catégorie
                            </CardDescription>
                        </div>
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                            <UserRound className="h-5 w-5" />
                        </span>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[300px] w-full">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart
                                    data={propertyTypes}
                                    margin={{ top: 10, right: 8, left: -16, bottom: 0 }}
                                >
                                    <CartesianGrid strokeDasharray="3 3" stroke="#eef3f7" vertical={false} />
                                    <XAxis
                                        dataKey="type"
                                        tickLine={false}
                                        axisLine={false}
                                        tick={{ fill: COLORS.slate, fontSize: 12 }}
                                    />
                                    <YAxis
                                        tickLine={false}
                                        axisLine={false}
                                        width={40}
                                        tick={{ fill: COLORS.slate, fontSize: 12 }}
                                        allowDecimals={false}
                                    />
                                    <Tooltip
                                        cursor={{ fill: '#eef3f7' }}
                                        content={<ChartTooltip formatter={(v) => `${v} biens`} />}
                                    />
                                    <Bar
                                        dataKey="total"
                                        name="Biens"
                                        fill={COLORS.blue}
                                        radius={[8, 8, 0, 0]}
                                        maxBarSize={56}
                                    />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
