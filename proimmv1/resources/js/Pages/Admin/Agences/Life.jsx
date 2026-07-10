import { Link } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Button } from '../../../components/ui/button';

export default function Life({ agence, activities = [], stats = {} }) {
    const counters = [
        ['Locataires', stats.nb_locataires ?? 0],
        ['Propriétaires', stats.nb_proprietaires ?? 0],
        ['Biens', stats.nb_biens ?? 0],
        ['Lots', stats.nb_lots ?? 0],
        ['Utilisateurs', stats.nb_utilisateurs ?? 0],
        ['Tickets', stats.nb_tickets ?? 0],
        ['Tickets résolus', stats.nb_tickets_resolus ?? 0],
        [
            'Taux résolution',
            (() => {
                const total = Number(stats.nb_tickets ?? 0);
                const resolus = Number(stats.nb_tickets_resolus ?? 0);
                return `${total > 0 ? Math.round((resolus / total) * 100) : 0}%`;
            })(),
        ],
    ];

    return (
        <AdminLayout title={`Vie de l'agence - ${agence?.name ?? agence?.nom ?? ''}`}>
            <div className="space-y-6">
                <Card className="border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#00559b] text-lg font-semibold text-white">
                                {String(agence?.name ?? agence?.nom ?? 'AG').slice(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Agence</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                                    {agence?.name ?? agence?.nom ?? 'Agence'}
                                </h1>
                                <p className="mt-1 text-sm text-slate-500">
                                    Code : {agence?.code_agence ?? agence?.code ?? 'N/A'} | Historique des activités
                                </p>
                            </div>
                        </div>

                        <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                            <Link href="/admin/agences">
                                Retour
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {counters.map(([label, value]) => (
                        <Card key={label} className="border-slate-200 shadow-sm">
                            <CardContent className="p-5">
                                <p className="text-sm text-slate-500">{label}</p>
                                <p className="mt-2 text-2xl font-semibold text-slate-900">{value}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="flex-row items-center justify-between border-b border-slate-200">
                            <div>
                                <CardTitle className="text-xl text-slate-900">Activités récentes</CardTitle>
                                <CardDescription className="text-slate-500">Chronologie des événements de l'agence</CardDescription>
                            </div>
                            <Button variant="outline" className="h-10 rounded-xl border-slate-200 px-4 text-slate-900">
                                <RefreshCw className="h-4 w-4" />
                                Actualiser
                            </Button>
                        </CardHeader>
                        <CardContent className="space-y-3 p-6">
                            {activities.length > 0 ? (
                                activities.map((activity, index) => (
                                    <div key={`${activity.title}-${index}`} className="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <span
                                            className="mt-1 h-3 w-3 rounded-full"
                                            style={{
                                                backgroundColor:
                                                    activity.color === 'green'
                                                        ? '#76c206'
                                                        : activity.color === 'yellow'
                                                        ? '#f59e0b'
                                                        : activity.color === 'red'
                                                        ? '#dc2626'
                                                        : '#00559b',
                                            }}
                                        />
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-start justify-between gap-4">
                                                <div className="min-w-0">
                                                    <p className="font-semibold text-slate-900">{activity.title}</p>
                                                    <p className="mt-1 text-sm text-slate-500">{activity.description}</p>
                                                    <p className="mt-1 text-xs text-slate-500">Par <strong>{activity.user}</strong></p>
                                                </div>
                                                <span className="text-xs text-slate-500">{activity.date}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                                    Aucune activité enregistrée pour cette agence.
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-xl text-slate-900">Résumé support</CardTitle>
                            <CardDescription className="text-slate-500">Indicateurs de suivi</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-3 p-6 sm:grid-cols-2">
                            {[
                                ['Tickets', stats.nb_tickets ?? 0],
                                ['Tickets résolus', stats.nb_tickets_resolus ?? 0],
                                ['Biens', stats.nb_biens ?? 0],
                                ['Lots', stats.nb_lots ?? 0],
                            ].map(([label, value]) => (
                                <div key={label} className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p className="text-sm text-slate-500">{label}</p>
                                    <p className="mt-2 text-2xl font-semibold text-slate-900">{value}</p>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
