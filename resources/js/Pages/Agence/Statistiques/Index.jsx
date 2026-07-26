import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import {
    Bar,
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
    Building2,
    CalendarClock,
    ChevronRight,
    Clock,
    FileText,
    PiggyBank,
    TrendingUp,
    Wallet,
    Wrench,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent } from '../../../components/ui/card';
import { cn } from '../../../lib/utils';

const BRAND = '#00559b';

const PERIODS = [
    { key: 'month', label: '1 - 30 Juin 2025' },
    { key: 'quarter', label: '1 Avr - 30 Juin 2025' },
    { key: 'year', label: '1 Jan - 31 Déc 2025' },
];

const TABS = [
    { key: 'overview', label: "Vue d'ensemble", icon: TrendingUp },
    { key: 'finances', label: 'Finances & Recouvrement', icon: Banknote },
    { key: 'maintenance', label: 'Gestion & Maintenance', icon: Wrench },
];

const PALETTE = [BRAND, '#22a6f2', '#5b6cff', '#94a3b8', '#f59e0b', '#ef4444'];

const MOCK = {
    patrimoine: {
        proprietes: 128,
        lots: 342,
        occupes: 301,
        libres: 41,
    },
    contrats: {
        locatairesActifs: 297,
        proprietaires: 96,
        contratsEnCours: 301,
        contratsExpirant: 14,
        nouveauxContrats: 9,
    },
    finances: {
        loyersAttendus: 48250000,
        loyersEncaisses: 41180000,
        impayes: 7070000,
        locatairesRetard: 23,
        arrieres: 12840000,
    },
    tresorerie: {
        depensesAgence: 6320000,
        soldeCaisse: 18450000,
        depensesParCategorie: [
            { label: 'Salaires', montant: 1980000 },
            { label: 'Loyer bureau', montant: 1450000 },
            { label: 'Autres', montant: 820000 },
            { label: 'Comm. & pub', montant: 1220000 },
            { label: 'Fournitures', montant: 870000 },
            { label: 'Déplacements', montant: 460000 },
        ],
    },
    maintenance: {
        interventionsOuvertes: 8,
        interventionsCloturees: 63,
        interventionsParType: [
            { label: 'Plomberie', valeur: 24 },
            { label: 'Électricité', valeur: 18 },
            { label: 'Serrurerie', valeur: 12 },
            { label: 'Peinture', valeur: 9 },
            { label: 'Autre', valeur: 8 },
        ],
    },
    evolution: [
        { mois: 'Juil', encaisse: 33, impaye: 4.2 },
        { mois: 'Août', encaisse: 41, impaye: 5.2 },
        { mois: 'Sep', encaisse: 38, impaye: 4.8 },
        { mois: 'Oct', encaisse: 44, impaye: 5.8 },
        { mois: 'Nov', encaisse: 42, impaye: 5.2 },
        { mois: 'Déc', encaisse: 45, impaye: 6.0 },
        { mois: 'Jan', encaisse: 48, impaye: 6.3 },
        { mois: 'Fév', encaisse: 46, impaye: 5.7 },
        { mois: 'Mar', encaisse: 49, impaye: 6.5 },
        { mois: 'Avr', encaisse: 50, impaye: 6.9 },
        { mois: 'Mai', encaisse: 48, impaye: 6.3 },
        { mois: 'Juin', encaisse: 47, impaye: 6.1 },
    ],
    topRentables: [
        { nom: 'Résidence Les Pins', valeur: 8450000 },
        { nom: 'Immeuble Bellevue', valeur: 6750000 },
        { nom: 'Villa Riviera 4', valeur: 6120000 },
        { nom: 'Immeuble Cocody Center', valeur: 5400000 },
    ],
    alertes: [
        { texte: '3 contrats expirent dans moins de 7 jours', niveau: 'warning' },
        { texte: '23 locataires en retard de paiement', niveau: 'danger' },
        { texte: '2 interventions maintenance en attente depuis 5 jours', niveau: 'warning' },
    ],
    activite: [
        { agent: 'Marco L.', action: "a clôturé l'intervention #D82", temps: 'il y a 22 min' },
        { agent: 'Fatou D.', action: 'a enregistré 3 paiements', temps: 'il y a 1 h' },
        { agent: 'Karim B.', action: 'a créé le contrat #F-021', temps: 'il y a 3 h' },
    ],
};

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
                    <Bar dataKey="encaisse" name="Encaissements" fill="rgba(0,85,155,0.78)" radius={[3, 3, 0, 0]} barSize={24} />
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

function OverviewTab({ d, tauxOccupation, metrics }) {
    return (
        <div className="flex flex-col gap-6">
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
                                items={d.topRentables.map((item) => ({
                                    nom: item.nom,
                                    valeur: item.valeur,
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

function FinancesTab({ d, tauxRecouvrement }) {
    return (
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
                        <p className="text-xs font-semibold uppercase tracking-wide text-[#00559b]">Loyers attendus (Juin)</p>
                        <p className="mt-1 text-4xl font-semibold text-slate-900">{fmtF(d.finances.loyersAttendus)}</p>

                        <div className="mt-4 space-y-3 border-t border-[#d7e4f1] pt-4">
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-slate-600">Encaissements</span>
                                <span className="font-semibold text-slate-900">{fmtF(d.finances.loyersEncaisses)}</span>
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
                    title="Répartition dépenses"
                    subtitle="Structure des charges de l'agence"
                    segments={d.tresorerie.depensesParCategorie}
                    centerValue={`${tauxRecouvrement}%`}
                    centerLabel="Recouvrement"
                    legendFormatter={(value) => fmtShort(value) + ' F'}
                />
            </aside>
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
                                <p className="text-xs text-slate-500">Clôturées (Juin)</p>
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
export default function Statistiques() {
    const [period, setPeriod] = useState('month');
    const [activeTab, setActiveTab] = useState('overview');
    const d = MOCK;

    const tauxOccupation = useMemo(() => Math.round((d.patrimoine.occupes / d.patrimoine.lots) * 100), [d.patrimoine]);
    const tauxRecouvrement = useMemo(() => Math.round((d.finances.loyersEncaisses / d.finances.loyersAttendus) * 100), [d.finances]);

    const overviewMetrics = [
        {
            icon: Building2,
            label: 'Patrimoine géré',
            value: `${d.patrimoine.proprietes} Propriétés`,
            sub: `${d.patrimoine.lots} lots au total`,
            tone: 'brand',
        },
        {
            icon: PiggyBank,
            label: 'Recouvrement Juin',
            value: `${tauxRecouvrement}%`,
            sub: '↑ +1.2% vs Mai',
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
            label: 'Baux à renouveler',
            value: `${d.contrats.contratsExpirant} Contrats`,
            sub: 'Échéance < 60j',
            tone: 'violet',
        },
    ];

    const currentPeriod = PERIODS.find((item) => item.key === period) ?? PERIODS[0];

    return (
        <AgenceLayout title="Statistiques">
            <div className="mx-auto max-w-[1520px] px-4 py-6 md:px-6">
                <div className="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Statistiques & Indicateurs</h1>
                        <p className="mt-1 text-sm text-slate-500">Vue d'ensemble des performances de l'agence — Juin 2025</p>
                    </div>

                    <div className="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                        <Button
                            type="button"
                            variant="outline"
                            className="h-10 justify-between rounded-xl border-[#d8e1ea] bg-white px-3 text-sm text-slate-600 shadow-sm"
                            onClick={() =>
                                setPeriod((current) => {
                                    const index = PERIODS.findIndex((item) => item.key === current);
                                    return PERIODS[(index + 1) % PERIODS.length].key;
                                })
                            }
                        >
                            <span className="flex items-center gap-2">
                                <CalendarClock className="h-4 w-4 text-[#00559b]" />
                                {currentPeriod.label}
                            </span>
                            <ChevronRight className="h-4 w-4 rotate-90 text-slate-400" />
                        </Button>

                        <div className="flex items-center gap-2">
                            <Button variant="outline" className="h-10 rounded-xl border-[#d8e1ea] bg-white px-3 text-sm text-slate-700 shadow-sm">
                                <FileText className="mr-2 h-4 w-4 text-red-500" />
                                PDF Rapport
                            </Button>
                            <Button variant="outline" className="h-10 rounded-xl border-[#d8e1ea] bg-white px-3 text-sm text-slate-700 shadow-sm">
                                <FileText className="mr-2 h-4 w-4 text-emerald-500" />
                                Excel Data
                            </Button>
                        </div>
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
                    <OverviewTab d={d} tauxOccupation={tauxOccupation} metrics={overviewMetrics} />
                ) : null}

                {activeTab === 'finances' ? <FinancesTab d={d} tauxRecouvrement={tauxRecouvrement} /> : null}

                {activeTab === 'maintenance' ? <MaintenanceTab d={d} /> : null}
            </div>
        </AgenceLayout>
    );
}

