import {
    ArrowUpRight,
    Building2,
    Minus,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { ChartArea, ChartContainer, ChartLegend } from '../../../components/ui/chart';
import { cn } from '../../../lib/utils';

const revenueSeries = [
    { label: 'Jan', value: 120000 },
    { label: 'Fév', value: 145000 },
    { label: 'Mar', value: 98000 },
    { label: 'Avr', value: 162000 },
    { label: 'Mai', value: 174000 },
];

const alerts = [
    {
        tone: 'danger',
        title: 'Pros Immo Yopougon - abonnement expiré',
        detail: 'Expiré depuis le 31/03/2026 · 50 000 FCFA',
        href: '/admin/abonnements/AGC-003',
    },
    {
        tone: 'warning',
        title: 'Pros Immo Bingerville - paiement à confirmer',
        detail: 'Échéance le 15/05/2026 · 72 000 FCFA',
        href: '/admin/abonnements/AGC-004',
    },
    {
        tone: 'info',
        title: 'Pros Immo Cocody - renouvellement dans 5 jours',
        detail: 'Échéance le 30/04/2026 · 50 000 FCFA',
        href: '/admin/abonnements/AGC-001',
    },
];

const activity = [
    {
        agency: 'Pros Immo Cocody',
        code: 'AGC-001',
        action: 'Abonnement créé',
        status: 'Actif',
        date: "Aujourd'hui, 09:40",
    },
    {
        agency: 'Pros Immo Bingerville',
        code: 'AGC-004',
        action: 'Paiement en attente',
        status: 'En attente',
        date: 'Hier, 17:15',
    },
    {
        agency: 'Pros Immo Plateau',
        code: 'AGC-002',
        action: 'Abonnement renouvelé',
        status: 'Actif',
        date: '27/04/2026',
    },
    {
        agency: 'Pros Immo Yopougon',
        code: 'AGC-003',
        action: 'Abonnement expiré',
        status: 'Expiré',
        date: '31/03/2026',
    },
];

const deadlines = [
    { agency: 'Pros Immo Cocody', code: 'AGC-001', dateFin: '30/04/2026', amount: 50000 },
    { agency: 'Pros Immo Plateau', code: 'AGC-002', dateFin: '05/05/2026', amount: 52000 },
    { agency: 'Pros Immo Bingerville', code: 'AGC-004', dateFin: '15/05/2026', amount: 72000 },
];

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value)) + ' FCFA';

const toneStyles = {
    success: 'border-[#c8d4de] bg-[#eef8df] text-[#4d8500]',
    info: 'border-[#c8d4de] bg-[#eaf4fb] text-[#00559b]',
    warning: 'border-[#c8d4de] bg-[#fff4d9] text-[#a06500]',
    danger: 'border-[#c8d4de] bg-[#fdecec] text-[#b42318]',
};

const alertDotStyles = {
    danger: 'bg-[#dc2626]',
    warning: 'bg-[#d97706]',
    info: 'bg-[#00559b]',
};

const statusStyles = {
    Actif: 'bg-[#eef8df] text-[#4d8500] border-[#c8d4de]',
    'En attente': 'bg-[#fff4d9] text-[#a06500] border-[#c8d4de]',
    Expiré: 'bg-[#fdecec] text-[#b42318] border-[#c8d4de]',
};

const kpiMeta = {
    'Revenu du mois': { icon: TrendingUp, suffix: 'vs mois précédent' },
    'Abonnements actifs': { icon: TrendingUp, suffix: 'ce mois' },
    'Paiements en attente': { icon: Minus, suffix: 'à confirmer' },
    'Alertes ouvertes': { icon: TrendingDown, suffix: 'Urgent' },
};

function KpiCard({ item }) {
    const meta = kpiMeta[item.label] ?? kpiMeta['Revenu du mois'];
    const Icon = meta.icon;
    const isNegative = item.tone === 'danger';

    return (
        <Card className="border-[#c8d4de] shadow-sm">
            <CardHeader className="space-y-3 pb-3">
                <div className="flex items-center justify-between gap-3">
                    <CardDescription className="text-[#5f7182]">{item.label}</CardDescription>
                    <Badge variant="outline" className={cn('rounded-full px-2.5 py-1', toneStyles[item.tone] ?? toneStyles.info)}>
                        {item.trend}
                    </Badge>
                </div>

                <CardTitle className="text-3xl tracking-tight text-[#0f172a]">{item.value}</CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
                <div className="flex items-center gap-2 text-sm text-[#5f7182]">
                    {isNegative ? <TriangleAlert className="h-4 w-4" /> : <Icon className="h-4 w-4" />}
                    <span>{meta.suffix}</span>
                </div>
            </CardContent>
        </Card>
    );
}

function SectionHeader({ title, description, action }) {
    return (
        <div className="flex items-start justify-between gap-4">
            <div>
                <CardDescription className="uppercase tracking-[0.28em] text-[#5f7182]">{description}</CardDescription>
                <CardTitle className="mt-2 text-xl text-[#0f172a]">{title}</CardTitle>
            </div>
            {action}
        </div>
    );
}

export default function Index({ admin, kpis = [] }) {
    const displayName = admin?.name ?? 'Administrateur';

    return (
        <AdminLayout title="Dashboard">
            <div className="space-y-6">
                <Card className="overflow-hidden border-[#c8d4de] shadow-sm">
                    <CardContent className="relative flex flex-col gap-6 p-6 md:flex-row md:items-center md:justify-between">
                        <div className="max-w-2xl">
                       
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#0f172a] md:text-4xl">
                                Bonjour, {displayName}
                            </h1>
                            <p className="mt-3 max-w-xl text-sm leading-6 text-[#5f7182] md:text-base">
                                Voici un résumé de l'activité du mois en cours, avec les abonnements à suivre et les
                                échéances importantes.
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Link href="/admin/abonnements/create">
                                    <Building2 className="h-4 w-4" />
                                    Nouvel abonnement
                                </Link>
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                className="h-11 rounded-xl border-[#c8d4de] bg-white px-4 text-[#0f172a]"
                            >
                                <Link href="/admin/statistiques">
                                    <ArrowUpRight className="h-4 w-4" />
                                    Voir les stats
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {kpis.map((item) => (
                        <KpiCard key={item.label} item={item} />
                    ))}
                </section>

                <section className="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
                    <Card className="border-[#c8d4de] shadow-sm">
                        <CardHeader className="flex-row items-start justify-between space-y-0">
                            <SectionHeader
                                title="Évolution des revenus"
                            
                            />
                        </CardHeader>

                        <CardContent className="space-y-4">
                            <ChartContainer>
                                <ChartArea data={revenueSeries} color="#00559b" />
                                <ChartLegend items={[{ label: 'Revenus mensuels', color: '#00559b' }]} />
                            </ChartContainer>
                        </CardContent>
                    </Card>

                    <Card className="border-[#c8d4de] shadow-sm">
                        <CardHeader>
                            <SectionHeader title="Alertes"  action={<Badge variant="secondary">{alerts.length} en cours</Badge>} />
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {alerts.map((alert) => (
                                <Button
                                    key={alert.title}
                                    asChild
                                    variant="ghost"
                                    className="h-auto w-full justify-start rounded-2xl border border-[#c8d4de] bg-[#f7fbfe] p-4 text-left hover:bg-white"
                                >
                                    <Link href={alert.href}>
                                        <span className={cn('mt-1 h-2.5 w-2.5 shrink-0 rounded-full', alertDotStyles[alert.tone])} />
                                        <span className="flex min-w-0 flex-1 flex-col items-start">
                                            <span className="truncate text-sm font-semibold text-[#0f172a]">
                                                {alert.title}
                                            </span>
                                            <span className="mt-1 text-sm text-[#5f7182]">{alert.detail}</span>
                                        </span>
                                    </Link>
                                </Button>
                            ))}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
                    <Card className="border-[#c8d4de] shadow-sm">
                        <CardHeader className="flex-row items-start  space-y-0">
                            <SectionHeader title="Activité récente" />
                        </CardHeader>

                        <CardContent className="px-0 pb-0">
                            <div className="overflow-hidden rounded-b-2xl">
                                <table className="w-full border-collapse">
                                    <thead className="bg-[#f7fbfe] text-left text-xs uppercase tracking-[0.2em] text-[#5f7182]">
                                        <tr>
                                            <th className="px-6 py-4 font-medium">Agence</th>
                                            <th className="px-6 py-4 font-medium">Action</th>
                                            <th className="px-6 py-4 font-medium">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-[#c8d4de]">
                                        {activity.map((row) => (
                                            <tr key={`${row.code}-${row.action}`} className="bg-white">
                                                <td className="px-6 py-4 align-top">
                                                    <p className="font-medium text-[#0f172a]">{row.agency}</p>
                                                    <p className="mt-1 text-xs text-[#5f7182]">
                                                        {row.code} · {row.date}
                                                    </p>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-[#5f7182]">{row.action}</td>
                                                <td className="px-6 py-4">
                                                    <Badge variant="outline" className={cn('rounded-full', statusStyles[row.status] ?? statusStyles.Expiré)}>
                                                        {row.status}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-[#c8d4de] shadow-sm">
                        <CardHeader className="flex-row items-start justify-between space-y-0">
                            <SectionHeader title="Prochaines échéances"  />
                        </CardHeader>

                        <CardContent className="space-y-3">
                            {deadlines.map((item) => (
                                <Button
                                    key={item.code}
                                    asChild
                                    variant="ghost"
                                    className="h-auto w-full justify-start rounded-2xl border border-[#c8d4de] bg-[#f7fbfe] p-4 text-left hover:bg-white"
                                >
                                    <Link href={`/admin/abonnements/${item.code}`}>
                                        <span className="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-[#00559b]" />
                                        <span className="flex min-w-0 flex-1 flex-col items-start">
                                            <span className="truncate text-sm font-semibold text-[#0f172a]">
                                                {item.agency}
                                            </span>
                                            <span className="mt-1 text-sm text-[#5f7182]">{item.dateFin}</span>
                                        </span>
                                        <span className="ml-3 shrink-0 text-sm font-semibold text-[#00559b]">
                                            {formatMoney(item.amount)}
                                        </span>
                                    </Link>
                                </Button>
                            ))}
                        </CardContent>
                    </Card>
                </section>

            </div>
        </AdminLayout>
    );
}
