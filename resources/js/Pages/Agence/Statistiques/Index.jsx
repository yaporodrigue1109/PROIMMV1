import { useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { Component } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    ComposedChart,
    Line,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import {
    AlertTriangle,
    Banknote,
    BriefcaseBusiness,
    Building2,
    CalendarClock,
    ChevronRight,
    Clock,
    FileText,
    PiggyBank,
    TrendingUp,
    Users,
    UserCheck,
    UserMinus,
    UserRound,
    Wallet,
    Wrench,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { cn } from '../../../lib/utils';

const BRAND = '#00559b';

const TABS = [
    { key: 'overview', label: "Vue d'ensemble", icon: TrendingUp },
    { key: 'proprietaires', label: 'Propriétaires', icon: UserRound },
    { key: 'locataires', label: 'Locataires', icon: Users },
    { key: 'personnel', label: 'Personnel', icon: BriefcaseBusiness },
    { key: 'finances', label: 'Finances & Recouvrement', icon: Banknote },
    { key: 'reversements', label: 'Tableau des reversements', icon: Wallet },
    { key: 'maintenance', label: 'Gestion & Maintenance', icon: Wrench },
];

const PALETTE = [BRAND, '#22a6f2', '#5b6cff', '#94a3b8', '#f59e0b', '#ef4444'];


function fmtF(n) {
    return new Intl.NumberFormat('fr-FR').format(n) + ' F';
}

function fmtShort(n) {
    if (n >= 1_000_000) {
        return (n / 1_000_000).toFixed(1).replace('.0', '') + ' M';
    }

    if (n >= 1_000) {
        return Math.round(n / 1_000) + ' k';
    }

    return String(n);
}

function SectionTitle({ icon: Icon, title, subtitle, action }) {
    return (
        <div className="mb-4 flex items-center justify-between gap-3">
            <div className="flex items-center gap-3">
                <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e8f1f9] text-[#00559b]">
                    <Icon className="h-5 w-5" />
                </span>
                <div>
                    <h2 className="text-sm font-semibold text-slate-900">{title}</h2>
                    {subtitle ? <p className="text-xs text-slate-500">{subtitle}</p> : null}
                </div>
            </div>
            {action}
        </div>
    );
}

function StatCard({ icon: Icon, label, value, sub, tone = 'brand' }) {
    const tones = {
        brand: { bg: '#e8f1f9', fg: BRAND },
        green: { bg: '#e8f7ed', fg: '#1d7f41' },
        amber: { bg: '#fff4df', fg: '#b86a00' },
        red: { bg: '#fdecec', fg: '#c03030' },
        slate: { bg: '#eef2f7', fg: '#475569' },
        violet: { bg: '#f1ebff', fg: '#6d28d9' },
    };

    const t = tones[tone] ?? tones.brand;

    return (
        <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
            <CardContent className="mt-6 flex items-start gap-3 p-4">
                <span
                    className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                    style={{ backgroundColor: t.bg, color: t.fg }}
                >
                    <Icon className="h-5 w-5" />
                </span>
                <div className="min-w-0">
                    <p className="truncate text-xs font-medium text-slate-500">{label}</p>
                    <p className="mt-1 text-xl font-semibold text-slate-900">{value}</p>
                    {sub ? <p className="mt-0.5 text-[11px] text-slate-500">{sub}</p> : null}
                </div>
            </CardContent>
        </Card>
    );
}

function ComboChart({ data }) {
    return (
        <div className="h-60 w-full min-w-[560px]">
            <ResponsiveContainer width="100%" height="100%">
                <ComposedChart data={data} margin={{ top: 8, right: 8, left: -8, bottom: 0 }}>
                    <CartesianGrid stroke="#eef2f6" vertical={false} />
                    <XAxis dataKey="mois" tickLine={false} axisLine={false} tick={{ fill: '#94a3b8', fontSize: 11 }} />
                    <YAxis tickLine={false} axisLine={false} tick={{ fill: '#94a3b8', fontSize: 11 }} width={32} />
                    <Tooltip
                        cursor={{ fill: 'rgba(0,85,155,0.04)' }}
                        contentStyle={{
                            borderRadius: '12px',
                            border: '1px solid #c8d4de',
                        }}
                    />
                    <Bar dataKey="loyers" name="Loyers encaissés" stackId="revenus" fill="rgba(0,85,155,0.78)" radius={[0, 0, 0, 0]} barSize={24} />
                    <Bar dataKey="ventes" name="Ventes" stackId="revenus" fill="#4d8500" radius={[3, 3, 0, 0]} barSize={24} />
                    <Line type="monotone" dataKey="impaye" name="Impayés" stroke="#ff5a6f" strokeWidth={2.5} dot={false} />
                </ComposedChart>
            </ResponsiveContainer>

            <div className="mt-2 flex flex-wrap items-center gap-5 pl-2 text-xs">
                <span className="flex items-center gap-2 text-slate-600">
                    <span className="h-2 w-4 rounded-full" style={{ backgroundColor: BRAND }} /> Encaissements
                </span>
                <span className="flex items-center gap-2 text-slate-600">
                    <span className="h-2 w-4 rounded-full bg-[#ff5a6f]" /> Impayés
                </span>
            </div>
        </div>
    );
}

function PeopleEvolutionChart({ data, label }) {
    return (
        <div className="h-64 w-full min-w-[420px]">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart data={data} margin={{ top: 8, right: 8, left: -16, bottom: 0 }}>
                    <CartesianGrid stroke="#eef2f6" vertical={false} />
                    <XAxis dataKey="mois" tickLine={false} axisLine={false} tick={{ fill: '#94a3b8', fontSize: 11 }} />
                    <YAxis allowDecimals={false} tickLine={false} axisLine={false} tick={{ fill: '#94a3b8', fontSize: 11 }} />
                    <Tooltip cursor={{ fill: 'rgba(0,85,155,0.04)' }} contentStyle={{ borderRadius: '12px', border: '1px solid #c8d4de' }} />
                    <Bar dataKey="total" name={label} fill={BRAND} radius={[5, 5, 0, 0]} barSize={28} />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}

function DonutCard({ icon: Icon = Building2, title, subtitle, segments, centerValue, centerLabel, legendFormatter = fmtShort }) {
    const getAmount = (item) => Number(item.value ?? item.montant ?? 0);
    const data = segments.map((segment) => ({
        label: segment.label,
        value: getAmount(segment),
    }));

    return (
        <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
            <CardContent className="mt-4 p-5">
                <SectionTitle icon={Icon} title={title} subtitle={subtitle} />
                <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div className="flex shrink-0 flex-col items-center">
                        <div className="h-44 w-44">
                            <ResponsiveContainer width="100%" height="100%">
                                <PieChart>
                                    <Pie
                                        data={data}
                                        dataKey="value"
                                        nameKey="label"
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={88}
                                        innerRadius={0}
                                        stroke="none"
                                    >
                                        {data.map((entry, index) => (
                                            <Cell key={entry.label} fill={PALETTE[index % PALETTE.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip
                                        formatter={(value, name) => [legendFormatter(Number(value)), name]}
                                        contentStyle={{
                                            borderRadius: '12px',
                                            border: '1px solid #c8d4de',
                                        }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                        <div className="mt-3 text-center">
                            <p className="text-2xl font-semibold text-slate-900">{centerValue}</p>
                            <p className="text-[11px] text-slate-500">{centerLabel}</p>
                        </div>
                    </div>

                    <div className="flex-1 space-y-2">
                        {segments.map((segment, index) => (
                            <div key={segment.label} className="flex items-center justify-between gap-3 text-sm">
                                <div className="flex items-center gap-2 text-slate-600">
                                    <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: PALETTE[index % PALETTE.length] }} />
                                    {segment.label}
                                </div>
                                <div className="font-medium text-slate-900">{legendFormatter(getAmount(segment))}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

function RankedList({ items, formatValue }) {
    const max = Math.max(...items.map((item) => item.valeur), 1);

    return (
        <div className="flex flex-col gap-3">
            {items.map((item, index) => (
                <div key={item.nom}>
                    <div className="mb-1 flex items-center justify-between gap-3 text-xs">
                        <span className="flex items-center gap-2 text-slate-600">
                            <span className="flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-500">
                                {String(index + 1).padStart(2, '0')}
                            </span>
                            <span className="truncate">{item.nom}</span>
                        </span>
                        <span className="font-medium text-slate-700">{formatValue(item.valeur)}</span>
                    </div>
                    <div className="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div className="h-full rounded-full" style={{ width: `${(item.valeur / max) * 100}%`, backgroundColor: BRAND }} />
                    </div>
                </div>
            ))}
        </div>
    );
}

function ActivityCard({ items, compact = false }) {
    return (
        <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
            <CardContent className="mt-4 p-5">
                <SectionTitle icon={Clock} title="Activité équipe" subtitle="Dernières actions de l'agence" />
                <ol className={cn('flex flex-col', compact ? 'gap-3' : 'gap-4')}>
                    {items.slice(0, compact ? 3 : items.length).map((item) => (
                        <li key={`${item.agent}-${item.temps}`} className="flex items-start gap-3">
                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#e8f1f9] text-xs font-semibold text-[#00559b]">
                                {item.agent
                                    .split(' ')
                                    .map((part) => part.slice(0, 1))
                                    .join('')
                                    .toUpperCase()}
                            </span>
                            <div>
                                <p className="text-sm text-slate-700">
                                    <span className="font-medium text-slate-900">{item.agent}</span> {item.action}
                                </p>
                                <p className="text-xs text-slate-400">{item.temps}</p>
                            </div>
                        </li>
                    ))}
                </ol>
                <Link href="/agence/support" className="mt-5 inline-flex items-center gap-1 text-sm font-medium" style={{ color: BRAND }}>
                    Voir tout le journal <ChevronRight className="h-4 w-4" />
                </Link>
            </CardContent>
        </Card>
    );
}

function AlertCard({ alertes }) {
    return (
        <Card className="rounded-2xl border-[#f5d5d5] bg-[#fff5f5] shadow-sm">
            <CardContent className="mt-4 p-5">
                <SectionTitle icon={AlertTriangle} title="Alertes incidents" subtitle="Points à traiter rapidement" />
                <div className="flex flex-col gap-2">
                    {alertes.map((alerte) => (
                        <div
                            key={alerte.texte}
                            className={cn(
                                'flex items-start justify-between gap-3 rounded-xl px-3 py-2.5',
                                alerte.niveau === 'danger' ? 'bg-[#ffe6e6]' : 'bg-white/70'
                            )}
                        >
                            <div className="text-sm text-slate-700">{alerte.texte}</div>
                            <span
                                className={cn(
                                    'shrink-0 rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-wide',
                                    alerte.niveau === 'danger'
                                        ? 'bg-[#ff6b6b] text-white'
                                        : 'bg-[#ff8a8a] text-white'
                                )}
                            >
                                {alerte.niveau === 'danger' ? 'Urgent' : 'Alerte'}
                            </span>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}

function OverviewTab({ d, tauxOccupation, metrics, searchTerm, setSearchTerm, sortBy, setSortBy, filteredTopProperties }) {
    return (
        <div className="flex flex-col gap-6">
            {/* <div className="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div className="flex flex-1 items-center gap-2 rounded-xl border border-[#dbe3ea] bg-white px-3 py-2 shadow-sm">
                    <FileText className="h-4 w-4 text-slate-400" />
                    <input
                        value={searchTerm}
                        onChange={(event) => setSearchTerm(event.target.value)}
                        placeholder="Rechercher une propriété, un locataire ou une maintenance"
                        className="w-full bg-transparent text-sm outline-none placeholder:text-slate-400"
                    />
                </div>

                <div className="flex items-center gap-2">
                    <span className="text-xs text-slate-500">Trier</span>
                    <select
                        value={sortBy}
                        onChange={(event) => setSortBy(event.target.value)}
                        className="rounded-xl border border-[#dbe3ea] bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                    >
                        <option value="recent">Plus récents</option>
                        <option value="amount">Montant</option>
                        <option value="name">Nom</option>
                    </select>
                </div>
            </div> */}

            {/* Indicateurs principaux */}
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {metrics.map((item) => {
                    const Icon = item.icon;

                    return (
                        <StatCard className="mt-4"
                            key={item.label}
                            icon={Icon}
                            label={item.label}
                            value={item.value}
                            sub={item.sub}
                            tone={item.tone}
                        />
                    );
                })}
            </div>

            {/* Deux colonnes équilibrées */}
            <div className="grid items-stretch gap-6 lg:grid-cols-2">
                {/* Colonne gauche */}
                <Card className="h-full min-w-0 rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                    <CardContent className="mt-4 flex h-full flex-col p-5">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p className="text-sm font-semibold text-slate-900">
                                    Performance des Encaissements
                                </p>

                                <p className="text-xs text-slate-500">
                                    Comparatif des encaissements et impayés
                                </p>
                            </div>

                            <Button
                                variant="ghost"
                                className="h-8 rounded-lg px-3 text-xs text-slate-500 hover:bg-slate-50"
                            >
                                Voir détails
                                <ChevronRight className="ml-1 h-4 w-4" />
                            </Button>
                        </div>

                        <div className="flex min-h-[350px] flex-1 items-center">
                            <ComboChart data={d.evolution} />
                        </div>
                    </CardContent>
                </Card>

                {/* Colonne droite */}
                <div className="grid h-full gap-6">
                    {/* Occupation */}
                    <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                        <CardContent className="mt-4 p-5">
                            <SectionTitle
                                icon={TrendingUp}
                                title="Occupation"
                                subtitle="Lots occupés et libres"
                            />

                            <div className="mt-3 flex items-end justify-between gap-4">
                                <div>
                                    <p className="text-4xl font-semibold text-slate-900">
                                        {tauxOccupation}%
                                    </p>

                                    <p className="mt-1 text-xs text-slate-500">
                                        Objectif : 92%
                                    </p>
                                </div>

                                <div className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
                                    {d.patrimoine.occupes} lots occupés
                                </div>
                            </div>

                            <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    className="h-full rounded-full bg-[#00559b]"
                                    style={{
                                        width: `${Math.min(tauxOccupation, 100)}%`,
                                    }}
                                />
                            </div>

                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex items-center justify-between gap-3">
                                    <span className="flex items-center gap-2 text-slate-600">
                                        <span className="h-2.5 w-2.5 rounded-full bg-emerald-500" />
                                        Occupés
                                    </span>

                                    <span className="font-medium text-slate-900">
                                        {d.patrimoine.occupes} lots
                                    </span>
                                </div>

                                <div className="flex items-center justify-between gap-3">
                                    <span className="flex items-center gap-2 text-slate-600">
                                        <span className="h-2.5 w-2.5 rounded-full bg-amber-400" />
                                        Libres
                                    </span>

                                    <span className="font-medium text-slate-900">
                                        {d.patrimoine.libres} lots
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Top rentabilité */}
                    <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                        <CardContent className="mt-4 p-5">
                            <SectionTitle
                                icon={PiggyBank}
                                title="Top rentabilité"
                                subtitle="Propriétés les plus performantes"
                            />

                            <RankedList
                                items={filteredTopProperties.map((item) => ({
                                    nom: item.propriete?.reference ?? `Propriété ${item.propriete_id ?? ''}`,
                                    valeur: Number(item.montant_total ?? 0),
                                }))}
                                formatValue={(value) => `${fmtShort(value)} F`}
                            />

                            <Link
                                href="/agence/proprietes"
                                className="mt-4 flex items-center justify-center rounded-xl bg-slate-50 py-2.5 text-xs font-medium text-slate-500 transition-colors hover:bg-slate-100 hover:text-[#00559b]"
                            >
                                Classement complet
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}

function PeopleOverviewTab({ title, subtitle, icon, metrics, evolution, evolutionLabel, distribution, distributionTitle, distributionSubtitle, total, totalLabel, actionHref, actionLabel, children }) {
    return (
        <div className="flex flex-col gap-6">
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {metrics.map((item) => (
                    <StatCard key={item.label} {...item} />
                ))}
            </div>

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(340px,0.9fr)]">
                <Card className="min-w-0 rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                    <CardContent className="mt-4 p-5">
                        <SectionTitle icon={icon} title={`Évolution des ${title.toLowerCase()}`} subtitle={`Nouvelles inscriptions par mois — ${subtitle}`} />
                        <PeopleEvolutionChart data={evolution} label={evolutionLabel} />
                    </CardContent>
                </Card>

                <DonutCard
                    icon={icon}
                    title={distributionTitle}
                    subtitle={distributionSubtitle}
                    segments={distribution}
                    centerValue={String(total)}
                    centerLabel={totalLabel}
                />
            </div>

            {actionHref ? (
                <Link href={actionHref} className="inline-flex w-fit items-center gap-1 text-sm font-medium text-[#00559b]">
                    {actionLabel} <ChevronRight className="h-4 w-4" />
                </Link>
            ) : null}

            {children}
        </div>
    );
}

function ReversementsYearTable({ rows = [], months = [] }) {
    const safeRows = (Array.isArray(rows) ? rows : Object.values(rows ?? {})).filter((row) => row && typeof row === 'object');
    const safeMonths = (Array.isArray(months) ? months : Object.values(months ?? {})).map((month) => String(month ?? ''));
    const rowMonths = (row) => Array.isArray(row?.months) ? row.months : Object.values(row?.months ?? {});
    const monthTotals = safeMonths.map((_, index) => safeRows.reduce((sum, row) => sum + Number(rowMonths(row)[index] ?? 0), 0));
    const grandTotal = safeRows.reduce((sum, row) => sum + Number(row?.total ?? 0), 0);

    return (
        <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
            <CardHeader className="border-b border-[#e2e8f0] py-4">
                <CardTitle className="text-sm text-[#0f172a]">Tableau récapitulatif des reversements par propriétaire</CardTitle>
                <CardDescription className="text-xs text-[#5f7182]">Montants reversés sur les 12 derniers mois, du plus récent au plus ancien.</CardDescription>
            </CardHeader>
            <CardContent className="mt-4 p-5">
                <div className="overflow-x-auto rounded-xl border border-[#e2e8f0]">
                        <table className="w-full min-w-[1300px] border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th className="sticky left-0 z-20 min-w-[230px] border-b border-r border-[#d8d4eb] bg-[#e8e2f3] px-4 py-4 text-left text-xs font-semibold uppercase text-[#3f3a52]">Propriétaire</th>
                                    {safeMonths.map((month, index) => (
                                        <th key={month} className={cn('min-w-[88px] border-b border-r border-[#e2e8f0] px-3 py-4 text-right text-xs font-semibold text-[#475569]', index === 0 ? 'bg-[#f6edc9]' : 'bg-[#f8fafc]')}>{month}</th>
                                    ))}
                                    <th className="min-w-[125px] border-b border-[#cfe2f3] bg-[#dcecf8] px-4 py-4 text-right text-xs font-semibold uppercase text-[#00559b]">Total annuel</th>
                                </tr>
                            </thead>
                            <tbody>
                                {safeRows.length ? safeRows.map((row) => (
                                    <tr key={row.proprietaire_id} className="odd:bg-white even:bg-[#fbfcfd]">
                                        <th className="sticky left-0 z-10 border-b border-r border-[#d8d4eb] bg-[#e8e2f3] px-4 py-4 text-left text-xs font-semibold uppercase text-[#3f3a52]">{row.proprietaire}</th>
                                        {safeMonths.map((month, index) => (
                                            <td key={`${row.proprietaire_id}-${month}`} className={cn('border-b border-r border-[#e2e8f0] px-3 py-4 text-right tabular-nums text-[#334155]', index === 0 ? 'bg-[#fbf4dc]' : '')}>{fmtF(rowMonths(row)[index] ?? 0)}</td>
                                        ))}
                                        <td className="border-b border-[#cfe2f3] bg-[#dcecf8] px-4 py-4 text-right font-semibold tabular-nums text-[#00559b]">{fmtF(row.total)}</td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan={14} className="bg-white px-4 py-10 text-center text-sm text-[#5f7182]">
                                            Aucun reversement effectué sur les 12 mois affichés pour cette agence.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th className="sticky left-0 z-20 border-r border-[#bfd8c4] bg-[#dcecf8] px-4 py-4 text-left text-xs font-bold uppercase text-[#0f172a]">Totaux</th>
                                    {monthTotals.map((total, index) => (
                                        <td key={`reversement-total-${index}`} className="border-r border-[#bfd8c4] bg-[#dcebd9] px-3 py-4 text-right font-bold tabular-nums text-[#244b2b]">{fmtF(total)}</td>
                                    ))}
                                    <td className="bg-[#a9d2bd] px-4 py-4 text-right font-bold tabular-nums text-[#163b29]">{fmtF(grandTotal)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
            </CardContent>
        </Card>
    );
}

class StatisticsTabBoundary extends Component {
    state = { hasError: false };

    static getDerivedStateFromError() {
        return { hasError: true };
    }

    componentDidCatch(error) {
        console.error('Erreur du tableau des reversements', error);
    }

    render() {
        if (this.state.hasError) {
            return (
                <Card className="rounded-2xl border-rose-200 bg-rose-50 shadow-sm">
                    <CardContent className="mt-4 p-6 text-sm text-rose-800">
                        Le tableau des reversements n’a pas pu être affiché. Actualisez la page puis réessayez.
                    </CardContent>
                </Card>
            );
        }

        return this.props.children;
    }
}

function FinancesTab({ d, tauxRecouvrement }) {
    return (
        <div className="space-y-6">
        <div className="grid items-stretch gap-6 lg:grid-cols-2">
            <Card className="h-full min-w-0 rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                <CardContent className="mt-4 flex h-full flex-col p-5">
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p className="text-sm font-semibold text-slate-900">Revenus Mensuels & Commissions</p>
                            <p className="text-xs text-slate-500">Encaissements et tendance du mois</p>
                        </div>
                        <Button variant="ghost" className="h-8 rounded-lg px-3 text-xs text-slate-500 hover:bg-slate-50">
                            CSV
                            <FileText className="ml-1 h-4 w-4" />
                        </Button>
                    </div>
                    <div className="flex min-h-[350px] flex-1 items-center">
                        <ComboChart data={d.evolution} />
                    </div>
                </CardContent>
            </Card>

            <aside className="grid h-full gap-6">
                <Card className=" rounded-2xl border-[#cfe0ef] bg-[#eef6fc] text-slate-900 shadow-sm">
                    <CardContent className="mt-4 p-5">
                            <p className="text-xs font-semibold uppercase tracking-wide text-[#00559b]">Loyers attendus (période sélectionnée)</p>
                        <p className="mt-1 text-4xl font-semibold text-slate-900">{fmtF(d.finances.loyersAttendus)}</p>

                        <div className="mt-4 space-y-3 border-t border-[#d7e4f1] pt-4">
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-slate-600">Encaissements</span>
                                <span className="font-semibold text-slate-900">{fmtF(d.finances.loyersEncaisses)}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-slate-600">Ventes réalisées ({d.finances.ventesNombre})</span>
                                <span className="font-semibold text-[#4d8500]">{fmtF(d.finances.ventesMontant)}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-slate-600">Impayés du mois</span>
                                <span className="font-semibold text-[#b42318]">{fmtF(d.finances.impayes)}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-slate-600">Arriérés totaux</span>
                                <span className="font-semibold text-slate-900">{fmtF(d.finances.arrieres)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <DonutCard
                    icon={Wallet}
                    title="Répartition des encaissements"
                    subtitle="Loyers et ventes de la période sélectionnée"
                    segments={d.tresorerie.encaissementsParCategorie}
                    centerValue={`${tauxRecouvrement}%`}
                    centerLabel="Recouvrement"
                    legendFormatter={(value) => fmtShort(value) + ' F'}
                />
            </aside>
        </div>
        </div>
    );
}

function MaintenanceTab({ d }) {
    const maintenancePieData = d.maintenance.interventionsParType.map((segment) => ({
        label: segment.label,
        value: Number(segment.valeur ?? 0),
    }));

    return (
        <div className="flex flex-col gap-6">
            <div className="grid gap-4 xl:grid-cols-3">
                <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                    <CardContent className="mt-4 p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Gestion des contrats</p>
                        <div className="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p className="text-4xl font-semibold text-slate-900">{d.contrats.contratsEnCours} / 214</p>
                                <p className="mt-1 text-sm text-slate-500">Lots sous contrat</p>
                            </div>
                            <div className="text-right">
                                <p className="text-2xl font-semibold text-[#0d8ae6]">88.3%</p>
                                <p className="text-xs text-slate-500">Taux d'occupation</p>
                            </div>
                        </div>
                        <div className="mt-4">
                            <p className="text-xs text-slate-500">NOUVEAUX (JUIN)</p>
                            <p className="text-xl font-semibold text-emerald-600">{d.contrats.nouveauxContrats}</p>
                            <div className="mt-4 flex justify-end">
                                <Link href="/agence/proprietes" className="text-xs font-semibold text-[#00559b]">
                                    Voir les baux
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                    <CardContent className="mt-4 p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Maintenance</p>
                        <div className="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p className="text-4xl font-semibold text-slate-900">{d.maintenance.interventionsOuvertes} / 36</p>
                                <p className="mt-1 text-sm text-slate-500">Interventions actives</p>
                            </div>
                            <div className="text-right">
                                <p className="text-2xl font-semibold text-[#0d8ae6]">{d.maintenance.interventionsCloturees}</p>
                                <p className="text-xs text-slate-500">Clôturées sur la période</p>
                            </div>
                        </div>
                        <Button className="mt-5 h-9 w-full rounded-xl bg-[#eaf4fb] text-[#00559b] hover:bg-[#deeffa]">
                            <CalendarClock className="mr-2 h-4 w-4" />
                            Programmer une intervention
                        </Button>
                    </CardContent>
                </Card>

                <ActivityCard items={d.activite} compact />
            </div>

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.9fr)_minmax(320px,0.95fr)]">
                <Card className="rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                    <CardContent className="mt-4 p-5">
                        <SectionTitle icon={Wrench} title="Répartition par type d'incident" subtitle="Suivi des demandes de maintenance" />
                        <div className="mt-4 flex flex-col gap-5 sm:flex-row sm:items-center">
                            <div className="mx-auto h-52 w-52 shrink-0">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={maintenancePieData}
                                            dataKey="value"
                                            nameKey="label"
                                            cx="50%"
                                            cy="50%"
                                            outerRadius={96}
                                            innerRadius={0}
                                            stroke="none"
                                        >
                                            {maintenancePieData.map((entry, index) => (
                                                <Cell key={entry.label} fill={PALETTE[index % PALETTE.length]} />
                                            ))}
                                        </Pie>
                                        <Tooltip
                                            formatter={(value, name) => [Number(value), name]}
                                            contentStyle={{
                                                borderRadius: '12px',
                                                border: '1px solid #c8d4de',
                                            }}
                                        />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>

                            <div className="flex-1 space-y-2">
                                {d.maintenance.interventionsParType.map((segment, index) => (
                                    <div key={segment.label} className="flex items-center justify-between gap-3 text-sm">
                                        <div className="flex items-center gap-2 text-slate-600">
                                            <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: PALETTE[index % PALETTE.length] }} />
                                            {segment.label}
                                        </div>
                                        <div className="font-medium text-slate-900">{segment.valeur} incidents</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <AlertCard alertes={d.alertes} />
            </div>
        </div>
    );
}
export default function Statistiques({
    stats = {},
    monthlyLabels = [],
    revenueSeries = [],
    salesMonthSeries = [],
    maintenanceMonthSeries = [],
    proprietairesMonthSeries = [],
    locatairesMonthSeries = [],
    personnelMonthSeries = [],
    loyersMonthSeries = [],
    personnelParRole = [],
    maintenanceSeries = [],
    topMaintenanceTypes = [],
    topProperties = [],
    recentTransactions = [],
    recentMaintenances = [],
    reversementsYearMatrix = [],
    reversementMonthLabels = [],
    year = new Date().getFullYear(),
    periode = new Date().toISOString().slice(0, 7),
    periodLabel = '',
}) {
    const [activeTab, setActiveTab] = useState('overview');
    const [searchTerm, setSearchTerm] = useState('');
    const [sortBy, setSortBy] = useState('recent');

    const d = useMemo(() => ({
        patrimoine: {
            proprietes: Number(stats.proprietes_total ?? 0),
            lots: Number(stats.lots_total ?? 0),
            occupes: Number(stats.portes_occupees ?? 0),
            libres: Number(stats.portes_libres ?? 0),
        },
        contrats: {
            locatairesActifs: Number(stats.locataires_actifs ?? 0),
            proprietaires: Number(stats.proprietaires_total ?? 0),
            contratsEnCours: Number(stats.portes_occupees ?? 0),
            contratsExpirant: 0,
            nouveauxContrats: Number(stats.locataires_ce_mois ?? 0),
        },
        finances: {
            loyersAttendus: Number(stats.reversements_attendu ?? stats.revenu_mois ?? 0),
            loyersEncaisses: Number(stats.revenu_mois ?? 0),
            ventesMontant: Number(stats.ventes_montant ?? 0),
            ventesNombre: Number(stats.ventes_nombre ?? 0),
            impayes: Math.max(Number(stats.reversements_attendu ?? 0) - Number(stats.revenu_mois ?? 0), 0),
            locatairesRetard: 0,
            arrieres: Number(stats.reversements_attendu ?? 0),
        },
        tresorerie: {
            depensesAgence: Number(stats.cout_maintenance_mois ?? 0),
            soldeCaisse: Number(stats.total_encaisse ?? 0),
            encaissementsParCategorie: [
                { label: 'Ventes', montant: Number(stats.ventes_montant ?? 0) },
                { label: 'Loyers', montant: Number(stats.revenu_mois ?? 0) },
            ].filter((item) => item.montant > 0),
        },
        maintenance: {
            interventionsOuvertes: Number(stats.maintenances_en_cours ?? 0),
            interventionsCloturees: Number(stats.maintenances_terminees ?? 0),
            interventionsParType: maintenanceSeries.map((segment) => ({
                label: segment.label,
                valeur: Number(segment.value ?? 0),
            })),
        },
        evolution: monthlyLabels.map((label, index) => ({
            mois: label,
            loyers: Number(revenueSeries[index] ?? 0),
            ventes: Number(salesMonthSeries[index] ?? 0),
            impaye: Math.max(Number(revenueSeries[index] ?? 0) * 0.12, 0),
        })),
        topRentables: topProperties.map((item) => ({
            nom: item.propriete?.reference ?? `Propriété ${item.propriete_id ?? ''}`,
            valeur: Number(item.montant_total ?? 0),
        })),
        alertes: [
            {
                texte: `${stats.reversements_en_attente ?? 0} reversements en attente`,
                niveau: 'warning',
            },
            {
                texte: `${stats.transactions_en_attente ?? 0} transactions en attente`,
                niveau: 'warning',
            },
            {
                texte: `${stats.maintenances_en_cours ?? 0} maintenances en cours`,
                niveau: 'warning',
            },
        ],
        activite: recentTransactions.slice(0, 3).map((item, index) => ({
            agent: item.tenant ?? `Paiement ${index + 1}`,
            action: `a enregistré un paiement de ${fmtF(Number(item.amount ?? 0))}`,
            temps: item.date ?? '—',
        })),
    }), [stats, monthlyLabels, revenueSeries, salesMonthSeries, maintenanceSeries, topProperties, recentTransactions]);

    const tauxOccupation = useMemo(() => {
        const total = Number(d.patrimoine.lots || d.patrimoine.proprietes || 0);
        const occupied = Number(d.patrimoine.occupes || 0);
        return total > 0 ? Math.round((occupied / total) * 100) : 0;
    }, [d.patrimoine]);

    const tauxRecouvrement = useMemo(() => {
        const attendu = Number(d.finances.loyersAttendus || 0);
        const encaisse = Number(d.finances.loyersEncaisses || 0);
        return attendu > 0 ? Math.round((encaisse / attendu) * 100) : 0;
    }, [d.finances]);

    const filteredTopProperties = useMemo(() => {
        const term = searchTerm.toLowerCase();
        const items = topProperties.filter((item) => {
            const label = `${item.propriete?.reference ?? ''} ${item.propriete?.adresse_complete ?? ''}`.toLowerCase();
            return !term || label.includes(term);
        });

        if (sortBy === 'amount') {
            return [...items].sort((a, b) => Number(b.montant_total ?? 0) - Number(a.montant_total ?? 0));
        }

        if (sortBy === 'name') {
            return [...items].sort((a, b) => (a.propriete?.reference ?? '').localeCompare(b.propriete?.reference ?? ''));
        }

        return items;
    }, [searchTerm, sortBy, topProperties]);

    const filteredRecentTransactions = useMemo(() => {
        const term = searchTerm.toLowerCase();
        const items = recentTransactions.filter((item) => {
            const label = `${item.tenant ?? ''} ${item.property ?? ''}`.toLowerCase();
            return !term || label.includes(term);
        });

        if (sortBy === 'amount') {
            return [...items].sort((a, b) => Number(b.amount ?? 0) - Number(a.amount ?? 0));
        }

        if (sortBy === 'name') {
            return [...items].sort((a, b) => (a.tenant ?? '').localeCompare(b.tenant ?? ''));
        }

        return items;
    }, [searchTerm, sortBy, recentTransactions]);

    const filteredTopMaintenanceTypes = useMemo(() => {
        const term = searchTerm.toLowerCase();
        const items = topMaintenanceTypes.filter((item) => !term || `${item.name ?? ''} ${item.categorie ?? ''}`.toLowerCase().includes(term));

        if (sortBy === 'amount') {
            return [...items].sort((a, b) => Number(b.montant_total ?? 0) - Number(a.montant_total ?? 0));
        }

        if (sortBy === 'name') {
            return [...items].sort((a, b) => (a.name ?? '').localeCompare(b.name ?? ''));
        }

        return items;
    }, [searchTerm, sortBy, topMaintenanceTypes]);

    const overviewMetrics = [
        {
            icon: Building2,
            label: 'Patrimoine géré',
            value: `${d.patrimoine.proprietes} Propriétés`,
            sub: `${d.patrimoine.lots} lots au total`,
            tone: 'brand',
        },
        {
            icon: Users,
            label: 'Propriétaires',
            value: `${stats.proprietaires_total ?? 0} propriétaires`,
            sub: `${stats.proprietaires_actifs ?? 0} actifs`,
            tone: 'violet',
        },
        {
            icon: Users,
            label: 'Locataires',
            value: `${stats.locataires_total ?? 0} locataires`,
            sub: `${stats.locataires_actifs ?? 0} actifs`,
            tone: 'green',
        },
        {
            icon: PiggyBank,
            label: 'Recouvrement',
            value: `${tauxRecouvrement}%`,
            sub: `Encaissements du mois ${fmtF(d.finances.loyersEncaisses)}`,
            tone: 'green',
        },
        {
            icon: Banknote,
            label: 'Loyers encaissés',
            value: fmtF(d.finances.loyersEncaisses),
            sub: `Sur ${fmtF(d.finances.loyersAttendus)} attendus`,
            tone: 'amber',
        },
        {
            icon: CalendarClock,
            label: 'Personnel',
            value: `${stats.personnel_total ?? 0} agents`,
            sub: `${stats.personnel_actifs ?? 0} actifs`,
            tone: 'violet',
        },
    ];

    const peopleEvolution = (series) => monthlyLabels.map((mois, index) => ({
        mois,
        total: Number(series[index] ?? 0),
    }));

    const proprietairesMetrics = [
        { icon: UserRound, label: 'Propriétaires enregistrés', value: String(stats.proprietaires_total ?? 0), sub: 'Portefeuille de l’agence', tone: 'violet' },
        { icon: UserCheck, label: 'Propriétaires actifs', value: String(stats.proprietaires_actifs ?? 0), sub: 'Liaisons actives', tone: 'green' },
        { icon: UserMinus, label: 'Propriétaires inactifs', value: String(Math.max(Number(stats.proprietaires_total ?? 0) - Number(stats.proprietaires_actifs ?? 0), 0)), sub: 'À relancer ou réactiver', tone: 'amber' },
        { icon: Building2, label: 'Lots propriétaires', value: String(stats.lots_total ?? 0), sub: 'Lots rattachés à l’agence', tone: 'brand' },
    ];

    const locatairesMetrics = [
        { icon: Users, label: 'Locataires enregistrés', value: String(stats.locataires_total ?? 0), sub: 'Tous les contrats', tone: 'brand' },
        { icon: UserCheck, label: 'Contrats actifs', value: String(stats.locataires_actifs ?? 0), sub: 'Baux en cours', tone: 'green' },
        { icon: UserCheck, label: 'À jour de loyer', value: String(stats.locataires_a_jour ?? 0), sub: 'Aucune échéance arrivée à terme', tone: 'green' },
        { icon: AlertTriangle, label: 'En retard de loyer', value: String(stats.locataires_en_retard ?? 0), sub: 'Au moins une échéance impayée', tone: 'red' },
        { icon: UserMinus, label: 'Contrats résiliés', value: String(stats.locataires_resilies ?? 0), sub: 'Historique des contrats', tone: 'red' },
        { icon: TrendingUp, label: 'Nouveaux ce mois', value: String(stats.locataires_ce_mois ?? 0), sub: 'Nouvelles entrées', tone: 'violet' },
    ];

    const personnelMetrics = [
        { icon: BriefcaseBusiness, label: 'Membres du personnel', value: String(stats.personnel_total ?? 0), sub: 'Équipe de l’agence', tone: 'violet' },
        { icon: UserCheck, label: 'Personnel actif', value: String(stats.personnel_actifs ?? 0), sub: 'Comptes opérationnels', tone: 'green' },
        { icon: UserMinus, label: 'Personnel inactif', value: String(Math.max(Number(stats.personnel_total ?? 0) - Number(stats.personnel_actifs ?? 0), 0)), sub: 'Comptes désactivés', tone: 'amber' },
        { icon: BriefcaseBusiness, label: 'Rôles attribués', value: String(personnelParRole.length), sub: 'Fonctions représentées', tone: 'brand' },
    ];

    return (
        <AgenceLayout title="Statistiques">
            <div className="mx-auto max-w-[1520px] px-4 py-6 md:px-6">
                <div className="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Statistiques & Indicateurs</h1>
                        <p className="mt-1 text-sm text-slate-500">Vue d'ensemble des performances de l'agence — {periodLabel}</p>
                    </div>

                    <div className="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                        <label className="flex h-10 items-center gap-2 rounded-xl border border-[#d8e1ea] bg-white px-3 text-sm text-slate-600 shadow-sm">
                            <CalendarClock className="h-4 w-4 text-[#00559b]" />
                            <input
                                type="month"
                                value={periode}
                                onChange={(event) => router.get('/agence/statistiques', { periode: event.target.value }, { preserveState: true, preserveScroll: true })}
                                className="bg-transparent text-sm text-slate-700 outline-none"
                            />
                        </label>

                        {/* <div className="flex items-center gap-2">
                            <Button variant="outline" className="h-10 rounded-xl border-[#d8e1ea] bg-white px-3 text-sm text-slate-700 shadow-sm">
                                <FileText className="mr-2 h-4 w-4 text-red-500" />
                                PDF Rapport
                            </Button>
                            <Button variant="outline" className="h-10 rounded-xl border-[#d8e1ea] bg-white px-3 text-sm text-slate-700 shadow-sm">
                                <FileText className="mr-2 h-4 w-4 text-emerald-500" />
                                Excel Data
                            </Button>
                        </div> */}
                    </div>
                </div>

                <div className="mb-6 flex flex-wrap gap-2 border-b border-[#dbe3ea]">
                    {TABS.map((tab) => {
                        const Icon = tab.icon;
                        const active = activeTab === tab.key;

                        return (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveTab(tab.key)}
                                className={cn(
                                    'border-b-2 px-1 py-2 text-sm font-medium transition-colors',
                                    active ? 'border-[#00559b] text-[#00559b]' : 'border-transparent text-slate-500 hover:text-slate-700'
                                )}
                            >
                                <span className="inline-flex items-center gap-2">
                                    <Icon className="h-4 w-4" />
                                    {tab.label}
                                </span>
                            </button>
                        );
                    })}
                </div>

                {activeTab === 'overview' ? (
                    <OverviewTab
                        d={d}
                        tauxOccupation={tauxOccupation}
                        metrics={overviewMetrics}
                        searchTerm={searchTerm}
                        setSearchTerm={setSearchTerm}
                        sortBy={sortBy}
                        setSortBy={setSortBy}
                        filteredTopProperties={filteredTopProperties}
                    />
                ) : null}

                {activeTab === 'proprietaires' ? (
                    <PeopleOverviewTab
                        title="Propriétaires"
                        subtitle={String(year)}
                        icon={UserRound}
                        metrics={proprietairesMetrics}
                        evolution={peopleEvolution(proprietairesMonthSeries)}
                        evolutionLabel="Nouveaux propriétaires"
                        distribution={[
                            { label: 'Actifs', value: Number(stats.proprietaires_actifs ?? 0) },
                            { label: 'Inactifs', value: Math.max(Number(stats.proprietaires_total ?? 0) - Number(stats.proprietaires_actifs ?? 0), 0) },
                        ]}
                        distributionTitle="Statut des propriétaires"
                        distributionSubtitle="Répartition du portefeuille"
                        total={stats.proprietaires_total ?? 0}
                        totalLabel="propriétaires"
                        actionHref="/agence/proprietaire"
                        actionLabel="Voir la liste des propriétaires"
                    />
                ) : null}

                {activeTab === 'locataires' ? (
                    <PeopleOverviewTab
                        title="Locataires"
                        subtitle={String(year)}
                        icon={Users}
                        metrics={locatairesMetrics}
                        evolution={peopleEvolution(locatairesMonthSeries)}
                        evolutionLabel="Nouveaux locataires"
                        distribution={[
                            { label: 'À jour', value: Number(stats.locataires_a_jour ?? 0) },
                            { label: 'En retard', value: Number(stats.locataires_en_retard ?? 0) },
                            { label: 'Résiliés', value: Number(stats.locataires_resilies ?? 0) },
                        ]}
                        distributionTitle="Situation des loyers"
                        distributionSubtitle="Suivi des échéances arrivées à terme"
                        total={stats.locataires_total ?? 0}
                        totalLabel="locataires"
                        actionHref="/agence/locataires"
                        actionLabel="Voir la liste des locataires"
                    >
                        <Card className="min-w-0 rounded-2xl border-[#dbe3ea] bg-white shadow-sm">
                            <CardContent className="mt-4 p-5">
                                <SectionTitle
                                    icon={Banknote}
                                    title="Impayés mensuels"
                                    subtitle={`Montants restant à régler par échéance — ${year}`}
                                />
                                <div className="overflow-x-auto">
                                    <ComboChart
                                        data={monthlyLabels.map((mois, index) => ({
                                            mois,
                                            encaisse: Number(loyersMonthSeries[index]?.encaisse ?? 0),
                                            impaye: Number(loyersMonthSeries[index]?.impaye ?? 0),
                                        }))}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </PeopleOverviewTab>
                ) : null}

                {activeTab === 'personnel' ? (
                    <PeopleOverviewTab
                        title="Personnel"
                        subtitle={String(year)}
                        icon={BriefcaseBusiness}
                        metrics={personnelMetrics}
                        evolution={peopleEvolution(personnelMonthSeries)}
                        evolutionLabel="Nouveaux membres"
                        distribution={personnelParRole.map((role) => ({ label: role.label, value: Number(role.value ?? 0) }))}
                        distributionTitle="Répartition par rôle"
                        distributionSubtitle="Fonctions de l’équipe"
                        total={stats.personnel_total ?? 0}
                        totalLabel="membres"
                        actionHref="/agence/personnel"
                        actionLabel="Gérer le personnel"
                    />
                ) : null}

                {activeTab === 'finances' ? <FinancesTab d={d} tauxRecouvrement={tauxRecouvrement} /> : null}

                {activeTab === 'reversements' ? (
                    <StatisticsTabBoundary>
                        <ReversementsYearTable rows={reversementsYearMatrix} months={reversementMonthLabels} />
                    </StatisticsTabBoundary>
                ) : null}

                {activeTab === 'maintenance' ? <MaintenanceTab d={d} /> : null}
            </div>
        </AgenceLayout>
    );
}
