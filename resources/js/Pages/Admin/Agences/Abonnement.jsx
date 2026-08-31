import { Link } from '@inertiajs/react';
import { Download, Settings2 } from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

export default function Abonnement({ agence, billing = {} }) {
    const subscription = agence?.subscription ?? agence?.abonnement;
    const modules = agence?.modules_payants ?? [];
    const modulesAvailable = subscription?.modules_available ?? modules.length;
    const base = Number(billing.montant_base ?? subscription?.prix_ht ?? subscription?.prix_mensuel_ht ?? 0);
    const modulesTotal = Number(billing.montant_modules ?? modules.reduce((sum, module) => sum + Number(module.prix ?? 0), 0));
    const total = Number(billing.montant_courant ?? base + modulesTotal);
    const history = billing.historique ?? [];

    return (
        <AdminLayout title="Abonnement agence">
            <div className="space-y-6">
                <Card className="border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Abonnement</p>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Abonnement de l'agence</h1>
                            <p className="mt-2 text-sm text-slate-500">Vue synthétique du plan, des options et de la facturation.</p>
                        </div>
                        <div className="flex flex-wrap gap-3">
                            <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/agences">
                                    Retour
                                </Link>
                            </Button>
                            <Button className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Download className="h-4 w-4" />
                                Exporter
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {[
                        ['Total facturé', formatMoney(billing.total_facture)],
                        ['Paiements réussis', String(billing.paiements_reussis ?? 0)],
                        ['Modules actifs', `${modules.length} / ${modulesAvailable}`],
                        ['Membre depuis', billing.membre_depuis ?? '—'],
                    ].map(([label, value]) => (
                        <Card key={label} className="border-slate-200 shadow-sm">
                            <CardContent className="p-5">
                                <p className="text-sm text-slate-500">{label}</p>
                                <p className="mt-2 text-2xl font-semibold text-slate-900">{value}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-xl text-slate-900">Plan actuel</CardTitle>
                            <CardDescription className="text-slate-500">Résumé du plan et des options</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-6">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Statut</p>
                                    <h3 className="mt-2 text-2xl font-semibold text-slate-900">{subscription?.name ?? 'Sans abonnement'}</h3>
                                    <p className="mt-2 text-sm text-slate-500">
                                        {subscription?.description_text ?? 'Aucune description disponible'}
                                    </p>
                                </div>
                                <Badge className={billing.statut === 'Actif'
                                    ? 'rounded-full border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700'
                                    : 'rounded-full border-red-200 bg-red-50 px-3 py-1 text-red-700'}>
                                    {billing.statut ?? 'Inactif'}
                                </Badge>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p className="text-sm text-slate-500">Modules</p>
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {modules.map((module) => (
                                        <Badge key={module.id ?? module.nom} variant="outline" className="rounded-full border-emerald-200 bg-emerald-50 text-emerald-700">
                                            {module.nom}{module.prix ? ` · ${formatMoney(module.prix)}` : ''}
                                        </Badge>
                                    ))}
                                    {modules.length === 0 ? <span className="text-sm text-slate-500">Aucun module complémentaire</span> : null}
                                </div>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p className="text-sm text-slate-500">Plan de base</p>
                                    <p className="mt-2 text-xl font-semibold text-slate-900">{formatMoney(base)}</p>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p className="text-sm text-slate-500">+ Modules actifs</p>
                                    <p className="mt-2 text-xl font-semibold text-slate-900">{formatMoney(modulesTotal)}</p>
                                </div>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                <p className="text-sm text-slate-500">Total / mois</p>
                                <p className="mt-2 text-3xl font-semibold text-[#00559b]">
                                    {formatMoney(total)} <span className="text-sm font-normal text-slate-500">/ mois</span>
                                </p>
                            </div>

                            <div className="flex flex-wrap gap-3">
                                <Button variant="outline" className="h-10 rounded-xl border-slate-200 px-4 text-slate-900">
                                    <Settings2 className="h-4 w-4" />
                                    Gérer
                                </Button>
                                <Button variant="outline" className="h-10 rounded-xl border-slate-200 px-4 text-slate-900">
                                    Renouvellement auto
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-xl text-slate-900">Historique de facturation</CardTitle>
                            <CardDescription className="text-slate-500">Cycles de facturation récents</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                        <tr>
                                            <th className="px-6 py-4 font-medium">Période</th>
                                            <th className="px-6 py-4 font-medium">Total</th>
                                            <th className="px-6 py-4 font-medium">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-200">
                                        {history.map((item) => (
                                            <tr key={item.reference}>
                                                <td className="px-6 py-4 text-slate-900">{item.debut ?? '—'} → {item.fin ?? '—'}</td>
                                                <td className="px-6 py-4 font-medium text-slate-900">{formatMoney(item.montant)}</td>
                                                <td className="px-6 py-4">
                                                    <Badge className={item.statut === 'Payé'
                                                        ? 'rounded-full border-emerald-200 bg-emerald-50 text-emerald-700'
                                                        : 'rounded-full border-amber-200 bg-amber-50 text-amber-700'}>
                                                        {item.statut}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}
                                        {history.length === 0 ? (
                                            <tr><td colSpan="3" className="px-6 py-8 text-center text-slate-500">Aucune facturation enregistrée.</td></tr>
                                        ) : null}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
