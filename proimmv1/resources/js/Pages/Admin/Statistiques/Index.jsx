import { useState } from 'react';
import { ArrowUpRight, BarChart3, CreditCard, Download, PieChart, Search, Ticket, TrendingUp } from 'lucide-react';

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

export default function Index() {
    const [tab, setTab] = useState('revenus');

    const revenueRows = [
        { mois: 'Janvier 2026', abo: 3, montant: 120000, pct: 69 },
        { mois: 'Fevrier 2026', abo: 4, montant: 145000, pct: 83 },
        { mois: 'Mars 2026', abo: 2, montant: 98000, pct: 57 },
        { mois: 'Avril 2026', abo: 5, montant: 162000, pct: 93 },
        { mois: 'Mai 2026', abo: 6, montant: 174000, pct: 100 },
    ];

    const subscriptions = [
        { agence: 'Pros Immo Cocody', code: 'AGC-001', plan: 'Standard', debut: '01/04/2026', fin: '30/04/2026', statut: 'Actif', montant: 50000 },
        { agence: 'Pros Immo Bingerville', code: 'AGC-004', plan: 'Standard', debut: '15/04/2026', fin: '15/05/2026', statut: 'En attente', montant: 72000 },
        { agence: 'Pros Immo Plateau', code: 'AGC-002', plan: 'Standard', debut: '05/04/2026', fin: '05/05/2026', statut: 'Actif', montant: 52000 },
        { agence: 'Pros Immo Yopougon', code: 'AGC-003', plan: 'Standard', debut: '01/03/2026', fin: '31/03/2026', statut: 'Expire', montant: 50000 },
        { agence: 'Pros Immo Marcory', code: 'AGC-005', plan: 'Standard', debut: '01/04/2026', fin: '30/04/2026', statut: 'Actif', montant: 55000 },
    ];

    const agencies = [
        { agence: 'Pros Immo Cocody', code: 'AGC-001', modules: 4, statut: 'Actif', montant: 50000, pct: 69 },
        { agence: 'Pros Immo Bingerville', code: 'AGC-004', modules: 3, statut: 'En attente', montant: 72000, pct: 100 },
        { agence: 'Pros Immo Plateau', code: 'AGC-002', modules: 1, statut: 'Actif', montant: 52000, pct: 72 },
        { agence: 'Pros Immo Yopougon', code: 'AGC-003', modules: 0, statut: 'Expire', montant: 50000, pct: 69 },
        { agence: 'Pros Immo Marcory', code: 'AGC-005', modules: 2, statut: 'Actif', montant: 55000, pct: 76 },
    ];

    const payments = [
        { agence: 'Pros Immo Cocody', code: 'AGC-001', date: '01 avr. 2026', montant: 50000, mode: 'Mobile Money', statut: 'Paye', ref: 'PAY-0041' },
        { agence: 'Pros Immo Plateau', code: 'AGC-002', date: '05 avr. 2026', montant: 52000, mode: 'Virement', statut: 'Paye', ref: 'PAY-0042' },
        { agence: 'Pros Immo Bingerville', code: 'AGC-004', date: '15 avr. 2026', montant: 72000, mode: 'N/A', statut: 'En attente', ref: 'PAY-0043' },
        { agence: 'Pros Immo Yopougon', code: 'AGC-003', date: '01 mar. 2026', montant: 50000, mode: 'Especes', statut: 'Paye', ref: 'PAY-0038' },
        { agence: 'Pros Immo Marcory', code: 'AGC-005', date: '02 avr. 2026', montant: 55000, mode: 'Mobile Money', statut: 'Paye', ref: 'PAY-0039' },
    ];

    const totalRevenus = revenueRows.reduce((sum, row) => sum + row.montant, 0);
    const maxRevenue = Math.max(...revenueRows.map((row) => row.montant));

    const statusBadge = (status) => {
        if (status === 'Actif' || status === 'Paye') return 'success';
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
                            <TabsList className="grid h-auto w-full grid-cols-4 rounded-2xl bg-slate-100 p-1">
                                <TabsTrigger value="revenus" className="rounded-xl">Revenus</TabsTrigger>
                                <TabsTrigger value="abonnements" className="rounded-xl">Abonnements</TabsTrigger>
                                <TabsTrigger value="agences" className="rounded-xl">Agences</TabsTrigger>
                                <TabsTrigger value="paiements" className="rounded-xl">Paiements</TabsTrigger>
                            </TabsList>
                        </Tabs>

                        <div className="flex items-center gap-3">
                            <Badge variant="outline" className="rounded-full">3 mois</Badge>
                            <span className="text-sm text-slate-500">Derniere mise a jour: aujourd&apos;hui a 08:42</span>
                        </div>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <Metric label="Revenu total" value={formatMoney(totalRevenus)} tone="text-[#00559b]" icon={TrendingUp} />
                    <Metric label="Ce mois" value={formatMoney(174000)} tone="text-emerald-600" icon={ArrowUpRight} />
                    <Metric label="Moyenne mensuelle" value={formatMoney(139800)} icon={CreditCard} />
                    <Metric label="Meilleur mois" value="Mai 2026" note="174 000 FCFA" icon={PieChart} />
                </div>

                <Tabs value={tab} onValueChange={setTab} className="space-y-6">
                    <TabsContent value="revenus" className="m-0">
                        <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Evolution mensuelle des revenus</CardTitle>
                                    <CardDescription>Janvier a mai 2026.</CardDescription>
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
                                    {[
                                        ['Pros Immo Marcory', 'Abonnement renouvele', 'Il y a 2h', '55 000'],
                                        ['Pros Immo Bingerville', 'Paiement en attente', 'Il y a 5h', '72 000'],
                                        ['Pros Immo Plateau', 'Virement recu', 'Hier', '52 000'],
                                        ['Pros Immo Cocody', 'Mobile Money confirme', '01 avr.', '50 000'],
                                    ].map(([agence, action, time, amount]) => (
                                        <div key={`${agence}-${time}`} className="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-semibold text-slate-900">{agence}</p>
                                                <p className="text-xs text-slate-500">{action}</p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-semibold text-slate-900">{amount} FCFA</p>
                                                <p className="text-xs text-slate-500">{time}</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="abonnements" className="m-0">
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
                                                <TableHead className="text-right">Montant</TableHead>
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
                                                    <TableCell className="text-right font-semibold text-slate-900">{formatMoney(row.montant)}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </ScrollArea>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="agences" className="m-0">
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
                                    <Metric label="Agences totales" value="8" icon={Ticket} />
                                    <Metric label="Avec abonnement" value="6" tone="text-emerald-600" icon={TrendingUp} />
                                    <Metric label="Sans abonnement" value="2" tone="text-amber-600" icon={Search} />
                                    <Metric label="Revenu max / agence" value={formatMoney(72000)} icon={BarChart3} />
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
