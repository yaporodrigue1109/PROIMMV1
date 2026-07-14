import { Link } from '@inertiajs/react';
import { ArrowLeft, Building2, CalendarClock, CreditCard, PencilLine, Ticket } from 'lucide-react';

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

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

const formatDate = (value) => {
    if (!value) return 'N/A';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('fr-FR');
};

const daysRemaining = (value) => {
    if (!value) return null;
    const end = new Date(value);
    if (Number.isNaN(end.getTime())) return null;
    return Math.ceil((end.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
};

const paymentMeta = {
    'Paye': { label: 'Paye', variant: 'success' },
    'A confirmer': { label: 'A confirmer', variant: 'warning' },
};

export default function Show({ abonnement = {}, history = [], plans = [] }) {
    const selectedPlan = plans.find((plan) => plan.nom === abonnement.plan) ?? null;
    const remaining = daysRemaining(abonnement.date_fin);
    const progress = remaining == null ? 0 : Math.max(8, Math.min(100, 100 - Math.max(0, remaining) * 3));

    return (
        <AdminLayout title="Detail abonnement">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#00559b] text-lg font-semibold text-white">
                                {String(abonnement.agence ?? 'AB').slice(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    Abonnement
                                </p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                                    {abonnement.agence ?? 'Abonnement sans agence'}
                                </h1>
                                <p className="mt-2 text-sm text-slate-500">
                                    {abonnement.code_agence ?? 'N/A'} · {abonnement.plan ?? 'Plan inconnu'} · {formatMoney(abonnement.montant)}
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/abonnements">
                                    <ArrowLeft className="h-4 w-4" />
                                    Retour
                                </Link>
                            </Button>
                            <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href={`/admin/agences/${abonnement.code_agence}`}>
                                    <Building2 className="h-4 w-4" />
                                    Voir l'agence
                                </Link>
                            </Button>
                            <Button asChild className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Link href={`/admin/abonnements/${abonnement.code_agence}/edit`}>
                                    <PencilLine className="h-4 w-4" />
                                    Modifier
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Plan" value={abonnement.plan ?? 'N/A'} />
                    <StatCard label="Montant" value={formatMoney(abonnement.montant)} />
                    <StatCard label="Cycle" value={abonnement.cycle ?? 'N/A'} />
                    <StatCard
                        label="Statut"
                        value={abonnement.statut ?? 'N/A'}
                        tone={abonnement.statut === 'Actif' ? 'text-emerald-600' : abonnement.statut === 'Expire' ? 'text-rose-600' : 'text-amber-600'}
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg">Periode et suivi</CardTitle>
                            <CardDescription>Informations de validite et notes internes.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-5 p-6">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <MetaBlock icon={CalendarClock} label="Date de debut" value={formatDate(abonnement.date_debut)} />
                                <MetaBlock icon={CalendarClock} label="Date de fin" value={formatDate(abonnement.date_fin)} />
                                <MetaBlock
                                    icon={Ticket}
                                    label="Restant"
                                    value={remaining > 0 ? `${remaining} jour(s)` : 'Expire'}
                                />
                                <MetaBlock
                                    icon={CreditCard}
                                    label="Paiement"
                                    value={abonnement.paiement ?? 'N/A'}
                                />
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div className="mb-3 flex items-center justify-between text-sm">
                                    <span className="font-medium text-slate-500">Progression de la periode</span>
                                    <span className="font-semibold text-slate-900">{progress}%</span>
                                </div>
                                <div className="h-2 overflow-hidden rounded-full bg-white">
                                    <div className="h-full rounded-full bg-[#00559b]" style={{ width: `${progress}%` }} />
                                </div>
                            </div>

                            <div className="rounded-2xl border border-slate-200 p-4">
                                <p className="text-sm font-medium text-slate-500">Notes</p>
                                <p className="mt-2 text-sm leading-6 text-slate-700">
                                    {abonnement.notes ?? 'Aucune note disponible.'}
                                </p>
                            </div>

                            {selectedPlan ? (
                                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p className="text-sm font-medium text-slate-500">Plan associe</p>
                                    <p className="mt-2 text-lg font-semibold text-slate-900">{selectedPlan.nom}</p>
                                    <p className="mt-1 text-sm leading-6 text-slate-600">{selectedPlan.description}</p>
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>

                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg">Modules actifs</CardTitle>
                            <CardDescription>Fonctionnalites incluses dans cet abonnement.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-6">
                            <div className="flex flex-wrap gap-2">
                                {(abonnement.modules ?? []).length > 0 ? (
                                    abonnement.modules.map((module) => (
                                        <Badge key={module} variant="outline" className="rounded-full">
                                            {module}
                                        </Badge>
                                    ))
                                ) : (
                                    <Badge variant="outline" className="rounded-full">
                                        Aucun module actif
                                    </Badge>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardHeader className="border-b border-slate-200">
                        <CardTitle className="text-lg">Historique de facturation</CardTitle>
                        <CardDescription>Derniers cycles relies a cet abonnement.</CardDescription>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-hidden rounded-b-3xl">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">Periode</th>
                                        <th className="px-6 py-4 font-medium">Montant</th>
                                        <th className="px-6 py-4 font-medium">Paiement</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200">
                                    {history.map((entry) => (
                                        <tr key={`${entry.periode}-${entry.montant}`}>
                                            <td className="px-6 py-4 text-slate-900">{entry.periode}</td>
                                            <td className="px-6 py-4 font-medium text-slate-900">{formatMoney(entry.montant)}</td>
                                            <td className="px-6 py-4">
                                                <Badge variant={(paymentMeta[entry.statut] ?? {}).variant ?? 'outline'}>
                                                    {paymentMeta[entry.statut]?.label ?? entry.statut}
                                                </Badge>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}

function StatCard({ label, value, tone = 'text-slate-900' }) {
    return (
        <Card className="border-slate-200 shadow-sm">
            <CardContent className="p-5">
                <p className="text-sm text-slate-500">{label}</p>
                <p className={`mt-2 text-lg font-semibold ${tone}`}>{value}</p>
            </CardContent>
        </Card>
    );
}

function MetaBlock({ icon: Icon, label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                <Icon className="h-3.5 w-3.5" />
                {label}
            </div>
            <p className="mt-3 text-sm font-semibold text-slate-900">{value}</p>
        </div>
    );
}
