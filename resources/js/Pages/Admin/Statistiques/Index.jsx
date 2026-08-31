import { useState } from 'react';
import { usePoll } from '@inertiajs/react';
import { ArrowUpRight, BarChart3, Building2, CreditCard, DoorOpen, Download, House, PieChart, Search, Ticket, TrendingUp, UserRound, Users } from 'lucide-react';
import { Bar, CartesianGrid, ComposedChart, Legend, Line, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { Progress } from '../../../components/ui/progress';
import { ScrollArea } from '../../../components/ui/scroll-area';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../components/ui/table';

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

export default function Index({ revenueRows = [], subscriptions = [], subscriptionMonthlyRows = [], agencies = [], agencyRankings = {}, payments = [], summary = {}, generalStats = {} }) {
    usePoll(30000, {
        only: ['revenueRows', 'subscriptions', 'subscriptionMonthlyRows', 'agencies', 'agencyRankings', 'payments', 'summary', 'generalStats'],
        preserveScroll: true,
        preserveState: true,
    });
    const [tab, setTab] = useState('general');

    const totalRevenus = revenueRows.reduce((sum, row) => sum + row.montant, 0);
    const maxRevenue = Math.max(1, ...revenueRows.map((row) => row.montant));
    const currentRevenue = revenueRows.at(-1)?.montant ?? 0;
    const averageRevenue = revenueRows.length ? totalRevenus / revenueRows.length : 0;
    const bestRevenue = revenueRows.reduce((best, row) => row.montant > (best?.montant ?? -1) ? row : best, null);
    const subscriptionTotalAmount = subscriptionMonthlyRows.reduce((sum, row) => sum + Number(row.montant ?? 0), 0);
    const subscriptionTotalEvents = subscriptionMonthlyRows.reduce((sum, row) => sum + Number(row.total ?? 0), 0);

    const statusBadge = (status) => {
        if (status === 'Actif' || status === 'Paye' || status === 'Payé') return 'success';
        if (status === 'En attente') return 'warning';
        return 'danger';
    };

    return (
        <AdminLayout title="Statistiques & Rapports">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="max-w-3xl space-y-3">
                            <div>
                                <h1 className="mt-4 text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Statistiques et rapports
                                </h1>
                            </div>
                        </div>

                        <div className="mt-4 flex flex-wrap gap-3">
                            <Button variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Download className="h-4 w-4" />
                                Exporter PDF
                            </Button>
                            <Button variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Download className="h-4 w-4" />
                                Exporter CSV
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <Tabs value={tab} onValueChange={setTab} className="w-full lg:max-w-3xl">
                            <TabsList className="grid h-auto w-full grid-cols-2 rounded-2xl bg-slate-100 p-1 sm:grid-cols-5">
                                <TabsTrigger value="general" className="rounded-xl">Vue générale</TabsTrigger>
                                <TabsTrigger value="revenus" className="rounded-xl">Revenus</TabsTrigger>
                                <TabsTrigger value="abonnements" className="rounded-xl">Abonnements</TabsTrigger>
                                <TabsTrigger value="agences" className="rounded-xl">Agences</TabsTrigger>
                                <TabsTrigger value="paiements" className="rounded-xl">Paiements</TabsTrigger>
                            </TabsList>
                        </Tabs>

                        <div className="flex items-center gap-3">
                            <Badge variant="outline" className="rounded-full">{summary.period_months ?? 0} mois</Badge>
                            <span className="text-sm text-slate-500">Dernière mise à jour : {summary.updated_at ?? '—'}</span>
                        </div>
                    </div>
                </div>

                <Tabs value={tab} onValueChange={setTab} className="space-y-6">
                    <TabsContent value="general" className="m-0 space-y-6">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <Metric label="Toutes les agences" value={generalStats.agences ?? 0} tone="text-[#00559b]" icon={Building2} />
                            <Metric label="Tous les locataires" value={generalStats.locataires ?? 0} icon={Users} />
                            <Metric label="Tous les utilisateurs" value={generalStats.utilisateurs ?? 0} icon={Users} />
                            <Metric label="Tous les propriétaires" value={generalStats.proprietaires ?? 0} icon={Users} />
                            <Metric label="Toutes les propriétés (biens)" value={generalStats.proprietes ?? 0} icon={House} />
                            <Metric label="Toutes les portes" value={generalStats.portes ?? 0} icon={DoorOpen} />
                            <Metric label="Portes occupées" value={generalStats.portes_occupees ?? 0} tone="text-emerald-600" icon={DoorOpen} />
                            <Metric label="Portes non occupées" value={generalStats.portes_libres ?? 0} tone="text-amber-600" icon={DoorOpen} />
                        </div>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Indicateurs complémentaires</CardTitle>
                                <CardDescription>Vue consolidée de l'ensemble du parc immobilier.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 sm:grid-cols-2 xl:grid-cols-4">
                                <Metric label="Taux d'occupation" value={`${generalStats.taux_occupation ?? 0}%`} icon={PieChart} />
                                <Metric label="Bâtiments" value={generalStats.batiments ?? 0} icon={Building2} />
                                <Metric label="Lots" value={generalStats.lots ?? 0} icon={House} />
                                <Metric label="Contrats actifs" value={generalStats.contrats_actifs ?? 0} icon={CreditCard} />
                                <Metric label="Tickets" value={generalStats.tickets ?? 0} icon={Ticket} />
                                <Metric label="Tickets résolus" value={generalStats.tickets_resolus ?? 0} icon={Ticket} />
                                <Metric label="Taux de résolution" value={`${generalStats.taux_resolution_tickets ?? 0}%`} icon={BarChart3} />
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="revenus" className="m-0">
                        <div className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <Metric label="Revenu total" value={formatMoney(totalRevenus)} tone="text-[#00559b]" icon={TrendingUp} />
                            <Metric label="Ce mois" value={formatMoney(currentRevenue)} tone="text-emerald-600" icon={ArrowUpRight} />
                            <Metric label="Moyenne mensuelle" value={formatMoney(averageRevenue)} icon={CreditCard} />
                            <Metric label="Meilleur mois" value={bestRevenue?.mois ?? '—'} note={bestRevenue ? formatMoney(bestRevenue.montant) : null} icon={PieChart} />
                        </div>
                        <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Evolution mensuelle des revenus</CardTitle>
                                    <CardDescription>{summary.period_label ?? 'Période non disponible'}</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4 p-6">
                                    {revenueRows.map((row) => (
                                        <div key={row.mois} className="space-y-2">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium text-slate-900">{row.mois}</span>
                                                <span className="text-slate-500">{formatMoney(row.montant)}</span>
                                            </div>
                                            <Progress value={(row.montant / maxRevenue) * 100} />
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Activite recente</CardTitle>
                                    <CardDescription>Derniers evenements financiers.</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3 p-6">
                                    {payments.slice(0, 4).map((payment) => (
                                        <div key={payment.ref} className="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-semibold text-slate-900">{payment.agence}</p>
                                                <p className="text-xs text-slate-500">{payment.statut}</p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-semibold text-slate-900">{formatMoney(payment.montant)}</p>
                                                <p className="text-xs text-slate-500">{payment.date}</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="abonnements" className="m-0 space-y-6">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <CardTitle className="text-lg">Évolution mensuelle des abonnements</CardTitle>
                                        <CardDescription>Nouveaux abonnements, renouvellements et montants sur les douze derniers mois.</CardDescription>
                                    </div>
                                    <div className="flex flex-wrap gap-3">
                                        <div className="rounded-2xl bg-[#eef6fb] px-4 py-3">
                                            <p className="text-xs uppercase tracking-wide text-slate-500">Abonnements</p>
                                            <p className="mt-1 text-lg font-semibold text-[#00559b]">{subscriptionTotalEvents}</p>
                                        </div>
                                        <div className="rounded-2xl bg-emerald-50 px-4 py-3">
                                            <p className="text-xs uppercase tracking-wide text-slate-500">Montant total</p>
                                            <p className="mt-1 text-lg font-semibold text-emerald-700">{formatMoney(subscriptionTotalAmount)}</p>
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="h-[340px] w-full">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <ComposedChart data={subscriptionMonthlyRows} margin={{ top: 12, right: 12, left: -12, bottom: 0 }}>
                                            <CartesianGrid stroke="#dbe4ec" strokeDasharray="4 6" vertical={false} />
                                            <XAxis dataKey="mois" axisLine={false} tickLine={false} tickMargin={12} tick={{ fill: '#64748b', fontSize: 12 }} />
                                            <YAxis yAxisId="count" allowDecimals={false} axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12 }} />
                                            <YAxis
                                                yAxisId="amount"
                                                orientation="right"
                                                axisLine={false}
                                                tickLine={false}
                                                tick={{ fill: '#047857', fontSize: 11 }}
                                                tickFormatter={(value) => `${Math.round(Number(value) / 1000)}k`}
                                            />
                                            <Tooltip content={<SubscriptionTooltip />} cursor={{ fill: '#eef6fb' }} />
                                            <Legend formatter={(value) => ({ nouveaux: 'Nouveaux abonnements', renouvellements: 'Renouvellements', montant: 'Montant' }[value] ?? value)} />
                                            <Bar yAxisId="count" dataKey="nouveaux" name="nouveaux" fill="#00559b" radius={[6, 6, 0, 0]} maxBarSize={34} />
                                            <Bar yAxisId="count" dataKey="renouvellements" name="renouvellements" fill="#f59e0b" radius={[6, 6, 0, 0]} maxBarSize={34} />
                                            <Line yAxisId="amount" type="monotone" dataKey="montant" name="montant" stroke="#059669" strokeWidth={3} dot={{ r: 4, fill: '#ffffff', strokeWidth: 2 }} />
                                        </ComposedChart>
                                    </ResponsiveContainer>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Liste des abonnements</CardTitle>
                                <CardDescription>Les comptes suivis par statut et periode.</CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <ScrollArea className="w-full">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-slate-50/80 hover:bg-slate-50">
                                                <TableHead>Agence</TableHead>
                                                <TableHead>Plan</TableHead>
                                                <TableHead>Debut</TableHead>
                                                <TableHead>Echeance</TableHead>
                                                <TableHead>Statut</TableHead>
                                                <TableHead className="text-right">Montant de base</TableHead>
                                                <TableHead className="text-right">Modules</TableHead>
                                                <TableHead className="text-right">Total</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {subscriptions.map((row) => (
                                                <TableRow key={row.code}>
                                                    <TableCell>
                                                        <div>
                                                            <p className="font-semibold text-slate-900">{row.agence}</p>
                                                            <p className="text-xs text-slate-500">{row.code}</p>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="text-slate-600">{row.plan}</TableCell>
                                                    <TableCell className="text-slate-600">{row.debut}</TableCell>
                                                    <TableCell className="text-slate-900">{row.fin}</TableCell>
                                                    <TableCell>
                                                        <Badge variant={statusBadge(row.statut)} className="rounded-full">{row.statut}</Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right text-slate-600">{formatMoney(row.montant_base)}</TableCell>
                                                    <TableCell className="text-right text-amber-600">{formatMoney(row.montant_modules)}</TableCell>
                                                    <TableCell className="text-right font-semibold text-emerald-700">{formatMoney(row.montant_total)}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </ScrollArea>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="agences" className="m-0">
                        <div className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <AgencyRanking title="Locataires actifs" rows={agencyRankings.locataires} icon={Users} />
                            <AgencyRanking title="Propriétés actives" rows={agencyRankings.proprietes} icon={House} />
                            <AgencyRanking title="Propriétaires actifs" rows={agencyRankings.proprietaires} icon={UserRound} />
                            <AgencyRanking title="Personnel actif" rows={agencyRankings.personnel} icon={Building2} />
                        </div>

                        <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Performance par agence</CardTitle>
                                    <CardDescription>Couverture abonnement et revenus mensuels.</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4 p-6">
                                    {agencies.map((row) => (
                                        <div key={row.code} className="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <div className="flex items-center justify-between gap-3">
                                                <div>
                                                    <p className="font-semibold text-slate-900">{row.agence}</p>
                                                    <p className="text-xs text-slate-500">{row.code}</p>
                                                </div>
                                                <Badge variant={statusBadge(row.statut)} className="rounded-full">{row.statut}</Badge>
                                            </div>
                                            <div className="flex items-center justify-between text-xs text-slate-500">
                                                <span>{row.modules} module(s)</span>
                                                <span>{formatMoney(row.montant)}</span>
                                            </div>
                                            <Progress value={row.pct} />
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Synthese</CardTitle>
                                    <CardDescription>Les principaux indicateurs du parc agences.</CardDescription>
                                </CardHeader>
                                <CardContent className="grid gap-4 p-6 sm:grid-cols-2">
                                    <Metric label="Agences totales" value={summary.total_agencies ?? 0} icon={Ticket} />
                                    <Metric label="Avec abonnement" value={summary.subscribed_agencies ?? 0} tone="text-emerald-600" icon={TrendingUp} />
                                    <Metric label="Sans abonnement" value={summary.unsubscribed_agencies ?? 0} tone="text-amber-600" icon={Search} />
                                    <Metric label="Revenu max / agence" value={formatMoney(summary.max_agency_revenue ?? 0)} icon={BarChart3} />
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="paiements" className="m-0">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Paiements recents</CardTitle>
                                <CardDescription>Les paiements confirmes et en attente.</CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <ScrollArea className="w-full">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-slate-50/80 hover:bg-slate-50">
                                                <TableHead>Agence</TableHead>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Mode</TableHead>
                                                <TableHead>Statut</TableHead>
                                                <TableHead className="text-right">Montant</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {payments.map((row) => (
                                                <TableRow key={row.ref}>
                                                    <TableCell>
                                                        <p className="font-semibold text-slate-900">{row.agence}</p>
                                                        <p className="text-xs text-slate-500">{row.code}</p>
                                                    </TableCell>
                                                    <TableCell className="text-slate-600">{row.date}</TableCell>
                                                    <TableCell className="text-slate-600">{row.mode}</TableCell>
                                                    <TableCell>
                                                        <Badge variant={statusBadge(row.statut)} className="rounded-full">{row.statut}</Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right font-semibold text-slate-900">{formatMoney(row.montant)}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </ScrollArea>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AdminLayout>
    );
}

function Metric({ label, value, note, tone = 'text-slate-900', icon: Icon }) {
    return (
        <Card className="border-slate-200 shadow-sm">
            <CardContent className="p-5">
                <div className="flex items-center justify-between gap-3">
                    <p className="text-sm text-slate-500">{label}</p>
                    <Icon className="h-4 w-4 text-slate-400" />
                </div>
                <p className={`mt-2 text-2xl font-semibold ${tone}`}>{value}</p>
                {note ? <p className="mt-1 text-xs text-slate-500">{note}</p> : null}
            </CardContent>
        </Card>
    );
}

function SubscriptionTooltip({ active, payload, label }) {
    if (!active || !payload?.length) return null;

    const row = payload[0]?.payload ?? {};

    return (
        <div className="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
            <p className="text-sm font-semibold text-slate-900">{label}</p>
            <p className="mt-2 text-sm text-[#00559b]">Nouveaux : {row.nouveaux ?? 0}</p>
            <p className="mt-1 text-sm text-amber-600">Renouvellements : {row.renouvellements ?? 0}</p>
            <p className="mt-1 text-sm font-semibold text-emerald-700">Montant : {formatMoney(row.montant ?? 0)}</p>
            <p className="mt-2 border-t border-slate-100 pt-2 text-xs text-slate-500">
                {row.agences ?? 0} agence(s) concernée(s)
            </p>
        </div>
    );
}

function AgencyRanking({ title, rows = [], icon: Icon }) {
    const rankingRows = Array.isArray(rows) ? rows : [];
    const maximum = Math.max(0, ...rankingRows.map((row) => Number(row.total ?? 0)));

    return (
        <Card className="rounded-3xl border-slate-200 shadow-sm">
            <CardHeader className="border-b border-slate-200 pb-4">
                <CardTitle className="flex items-center gap-2 text-base">
                    <Icon className="h-4 w-4 text-[#00559b]" />
                    {title}
                </CardTitle>
                <CardDescription>Classement décroissant par agence.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-2 p-4">
                {rankingRows.map((row, index) => {
                    const isMaximum = maximum > 0 && Number(row.total) === maximum;

                    return (
                        <div key={row.code} className={`flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5 ${isMaximum ? 'border-[#9bc4df] bg-[#eef6fb]' : 'border-slate-200 bg-slate-50'}`}>
                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-slate-900">{index + 1}. {row.agence}</p>
                                <p className="text-xs text-slate-500">{row.code}</p>
                            </div>
                            <div className="text-right">
                                <p className={`text-lg font-bold ${isMaximum ? 'text-[#00559b]' : 'text-slate-700'}`}>{row.total}</p>
                                {isMaximum ? <p className="text-[10px] font-semibold uppercase text-[#00559b]">Plus élevé</p> : null}
                            </div>
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
