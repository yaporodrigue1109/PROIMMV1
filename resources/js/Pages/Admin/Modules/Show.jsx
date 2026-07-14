import { Link } from '@inertiajs/react';
import { ArrowLeft, PencilLine, ShieldCheck, Tags, Users } from 'lucide-react';

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

export default function Show({ module = {} }) {
    const isActive = module.statut === 'Actif';

    return (
        <AdminLayout title="Detail du module">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">
                                {module.code ?? 'N/A'}
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                                {module.nom ?? 'Module sans nom'}
                            </h1>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                                {module.description ?? 'Aucune description disponible.'}
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/modules">
                                    <ArrowLeft className="h-4 w-4" />
                                    Retour
                                </Link>
                            </Button>
                            <Button asChild className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Link href={`/admin/modules/${module.code}/edit`}>
                                    <PencilLine className="h-4 w-4" />
                                    Modifier
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <MetricCard label="Categorie" value={module.categorie ?? 'N/A'} />
                    <MetricCard label="Tarif" value={`${formatMoney(module.prix)} / ${String(module.cycle ?? 'mois').toLowerCase()}`} />
                    <MetricCard label="Agences actives" value={module.agences ?? 0} />
                    <MetricCard
                        label="Statut"
                        value={module.statut ?? 'N/A'}
                        tone={isActive ? 'text-emerald-600' : 'text-rose-600'}
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg">Configuration du module</CardTitle>
                            <CardDescription>Informations techniques et tarifaires.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 p-6 sm:grid-cols-2">
                            <InfoBlock icon={Tags} label="Categorie" value={module.categorie ?? 'N/A'} />
                            <InfoBlock icon={Users} label="Agences actives" value={module.agences ?? 0} />
                            <InfoBlock icon={ShieldCheck} label="Code technique" value={module.code ?? 'N/A'} />
                            <InfoBlock icon={Tags} label="Cycle" value={module.cycle ?? 'Mensuel'} />
                            <div className="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p className="text-sm font-medium text-slate-500">Statut</p>
                                <Badge
                                    variant={isActive ? 'success' : 'danger'}
                                    className="mt-3 rounded-full"
                                >
                                    {module.statut ?? 'N/A'}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg">Permissions incluses</CardTitle>
                            <CardDescription>{(module.permissions ?? []).length} règle(s) de visibilité et d’action.</CardDescription>
                        </CardHeader>
                        <CardContent className="p-6">
                            <div className="flex flex-wrap gap-2">
                                {(module.permissions ?? []).map((permission) => (
                                    <Badge key={permission} variant="outline" className="rounded-full">
                                        {permission}
                                    </Badge>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}

function MetricCard({ label, value, tone = 'text-slate-900' }) {
    return (
        <Card className="border-slate-200 shadow-sm">
            <CardContent className="p-5">
                <p className="text-sm text-slate-500">{label}</p>
                <p className={`mt-2 text-lg font-semibold ${tone}`}>{value}</p>
            </CardContent>
        </Card>
    );
}

function InfoBlock({ icon: Icon, label, value }) {
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
