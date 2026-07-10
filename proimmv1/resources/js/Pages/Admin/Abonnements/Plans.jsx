import { Link } from '@inertiajs/react';
import { ArrowLeft, Check, Sparkles, Ticket } from 'lucide-react';

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

export default function Plans({ plans = [], stats = {} }) {
    const totalPlans = plans.length;
    const highlighted = plans.find((plan) => plan.highlight) ?? plans[0] ?? null;
    const allModules = Array.from(new Set(plans.flatMap((plan) => plan.modules ?? [])));

    return (
        <AdminLayout title="Plans abonnement">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-3">
                            <div className="inline-flex items-center gap-2 rounded-full border border-[#d8e3eb] bg-[#eef6fb] px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#00559b]">
                                <Sparkles className="h-3.5 w-3.5" />
                                Plans
                            </div>
                            <div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
                                    Catalogue des abonnements
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                                    Comparez les offres, leurs tarifs et leurs modules avant de rattacher un compte a un plan.
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
                            <Button asChild className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Link href="/admin/abonnements/create">
                                    <Ticket className="h-4 w-4" />
                                    Nouvel abonnement
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <MetricCard label="Plans disponibles" value={totalPlans} note="offres actives" />
                    <MetricCard label="Abonnements actifs" value={stats.actifs ?? 0} note="comptes suivis" tone="text-emerald-600" />
                    <MetricCard label="En attente" value={stats.attente ?? 0} note="paiements a confirmer" tone="text-amber-600" />
                    <MetricCard label="Revenu mensuel" value={formatMoney(stats.revenu ?? 0)} note="sur les plans actifs" tone="text-[#00559b]" />
                </div>

                <div className="grid gap-6 xl:grid-cols-3">
                    {plans.map((plan) => (
                        <Card
                            key={plan.nom}
                            className={`rounded-3xl shadow-sm ${
                                plan.highlight ? 'border-[#00559b] ring-1 ring-[#00559b]/10' : 'border-slate-200'
                            }`}
                        >
                            <CardHeader className="space-y-3 border-b border-slate-200 pb-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <CardTitle className="text-2xl">{plan.nom}</CardTitle>
                                        <CardDescription className="mt-2">{plan.description}</CardDescription>
                                    </div>
                                    {plan.highlight ? (
                                        <Badge variant="secondary" className="rounded-full">
                                            Populaire
                                        </Badge>
                                    ) : (
                                        <Badge variant="outline" className="rounded-full">
                                            Standard
                                        </Badge>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-5 p-6">
                                <div>
                                    <p className="text-sm text-slate-500">Tarif</p>
                                    <p className="mt-2 text-3xl font-semibold text-slate-900">
                                        {formatMoney(plan.prix)}
                                        <span className="text-sm font-normal text-slate-500"> / {plan.cycle ?? 'mois'}</span>
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    {(plan.modules ?? []).map((module) => (
                                        <div key={module} className="flex items-center gap-2 text-sm text-slate-700">
                                            <Check className="h-4 w-4 text-emerald-600" />
                                            {module}
                                        </div>
                                    ))}
                                </div>

                                <Button
                                    asChild
                                    variant={plan.highlight ? 'default' : 'outline'}
                                    className={`h-11 w-full rounded-xl ${
                                        plan.highlight ? 'bg-[#00559b] text-white hover:bg-[#004980]' : 'border-slate-200 text-slate-900'
                                    }`}
                                >
                                    <Link href="/admin/abonnements/create">
                                        Choisir ce plan
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg">Plan phare</CardTitle>
                            <CardDescription>Le plan mis en avant dans le catalogue.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-6">
                            {highlighted ? (
                                <>
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p className="text-sm text-slate-500">Selection actuelle</p>
                                        <p className="mt-2 text-2xl font-semibold text-slate-900">{highlighted.nom}</p>
                                        <p className="mt-2 text-sm leading-6 text-slate-600">{highlighted.description}</p>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {(highlighted.modules ?? []).map((module) => (
                                            <Badge key={module} variant="outline" className="rounded-full">
                                                {module}
                                            </Badge>
                                        ))}
                                    </div>
                                </>
                            ) : (
                                <div className="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                                    Aucun plan configure.
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg">Modules disponibles</CardTitle>
                            <CardDescription>Ensemble des options presentes dans le catalogue.</CardDescription>
                        </CardHeader>
                        <CardContent className="p-6">
                            <div className="grid gap-3 sm:grid-cols-2">
                                {allModules.map((module) => (
                                    <div key={module} className="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                        <Check className="h-4 w-4 text-emerald-600" />
                                        {module}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}

function MetricCard({ label, value, note, tone = 'text-slate-900' }) {
    return (
        <Card className="border-slate-200 shadow-sm">
            <CardContent className="p-5">
                <p className="text-sm text-slate-500">{label}</p>
                <p className={`mt-2 text-2xl font-semibold ${tone}`}>{value}</p>
                <p className="mt-1 text-xs text-slate-500">{note}</p>
            </CardContent>
        </Card>
    );
}
