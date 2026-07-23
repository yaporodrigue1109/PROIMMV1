import { Head, router, usePage } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { useMemo, useState } from 'react';

import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { cn } from '../../../lib/utils';

const formatMoney = (value) =>
    `${new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))} FCFA`;

const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isNaN(parsed) ? fallback : parsed;
};

export default function Index({ tarifs = {}, draft = null }) {
    const page = usePage();
    const agency = page?.props?.auth?.user?.agence ?? null;
    const subscriptionFlow = page?.props?.subscription_flow ?? {};
    const agencyName = agency?.name ?? agency?.nom ?? agency?.raison_sociale ?? agency?.nom_agence ?? 'Mon agence';
    const pageTitle = subscriptionFlow.title ?? 'Souscrire a un abonnement';

    const plan = tarifs.plan ?? {};
    const durations = Array.isArray(tarifs.durees) ? tarifs.durees : [];
    const modules = Array.isArray(tarifs.modules) ? tarifs.modules : [];

    const initialDuration = toInt(draft?.duree_mois, durations[0]?.nombre_mois ?? 12);
    const [duration, setDuration] = useState(initialDuration);
    const [selectedModules, setSelectedModules] = useState(
        Array.isArray(draft?.modules_ids) ? draft.modules_ids.map((id) => toInt(id)).filter(Boolean) : []
    );

    const selectedDuration = useMemo(
        () => durations.find((item) => toInt(item.nombre_mois) === toInt(duration)) ?? durations[0] ?? null,
        [durations, duration]
    );

    const selectedModuleItems = useMemo(
        () => modules.filter((module) => selectedModules.includes(toInt(module.id))),
        [modules, selectedModules]
    );

    const durationMonths = toInt(duration);

    const baseTotal = Number(selectedDuration?.prix_total ?? 0) > 0
        ? Number(selectedDuration?.prix_total ?? 0)
        : Number(plan.prix_mensuel ?? 0) * Number(durationMonths ?? 0);

    const modulesTotal = selectedModuleItems.reduce((sum, module) => {
        return sum + Number(getModuleTotal(module, durationMonths));
    }, 0);

    const total = baseTotal + modulesTotal;
    const badgeLabel = getSubscriptionFlowBadgeLabel(subscriptionFlow.state);

    function getModuleTotal(module, months = durationMonths) {
        const monthlyPrice = Number(module?.prix_mensuel ?? 0);
        const fallbackTotal = monthlyPrice * Number(months ?? 0);

        return Number(module?.prix_total ?? fallbackTotal ?? 0);
    }

    const toggleModule = (moduleId) => {
        const id = toInt(moduleId);
        setSelectedModules((current) =>
            current.includes(id)
                ? current.filter((item) => item !== id)
                : [...current, id]
        );
    };

    const submitCheckout = (event) => {
        event.preventDefault();

        router.post('/agence/abonnement/checkout', {
            duree_mois: duration,
            modules: selectedModules,
        });
    };

    return (
        <AgenceLayout title={pageTitle}>
            <Head title={pageTitle} />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 pb-10">
                <Card className="rounded-[1.5rem] border-[#d7e3ee] bg-[#f8fbfe] shadow-sm">
                    <CardContent className="mt-4 flex flex-col gap-4 p-6 md:flex-row md:items-center md:justify-between">
                        <div className="space-y-2">
                            <div className="flex items-center gap-3">
                                <h1 className="text-xl font-semibold text-[#0f172a]">{pageTitle}</h1>
                                {subscriptionFlow.state ? (
                                    <Badge
                                        variant={subscriptionFlow.tone === 'danger' ? 'destructive' : 'secondary'}
                                        className="rounded-full px-3 py-1"
                                    >
                                        {badgeLabel}
                                    </Badge>
                                ) : null}
                            </div>
                            <p className="max-w-3xl text-sm leading-6 text-[#5f7182]">
                                {subscriptionFlow.description ?? 'Choisissez votre formule, les modules utiles, puis passez au paiement.'}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <section className="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_360px]">
                    <div className="space-y-6">
                        <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg text-[#0f172a]">1. Choisir la duree</CardTitle>
                                <CardDescription>La duree met a jour automatiquement les montants.</CardDescription>
                            </CardHeader>
                            <CardContent className="mt-4 space-y-4 p-6">
                                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    {durations.map((item) => {
                                        const months = toInt(item.nombre_mois);
                                        const active = months === toInt(duration);

                                        return (
                                            <button
                                                key={item.id ?? months}
                                                type="button"
                                                onClick={() => setDuration(months)}
                                                className={cn(
                                                    'rounded-2xl border p-4 text-left transition',
                                                    active
                                                        ? 'border-[#00559b] bg-[#eaf4fb] shadow-sm'
                                                        : 'border-slate-200 bg-white hover:border-[#9dbfda]'
                                                )}
                                            >
                                                <div className="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p className="text-sm font-semibold text-[#0f172a]">{item.label ?? `${months} mois`}</p>
                                                        <p className="mt-1 text-xs text-[#5f7182]">Cycle de souscription</p>
                                                    </div>
                                                    {active ? <Check className="h-4 w-4 text-[#00559b]" /> : null}
                                                </div>
                                                <Separator className="my-4" />
                                                <p className="text-sm font-semibold text-[#00559b]">
                                                    {formatMoney(item.prix_total ?? Number(plan.prix_mensuel ?? 0) * months)}
                                                </p>
                                            </button>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg text-[#0f172a]">2. Choisir les modules</CardTitle>
                                <CardDescription>Les modules seront ajoutes a votre abonnement principal.</CardDescription>
                            </CardHeader>
                            <CardContent className="mt-4 p-6">
                                {modules.length ? (
                                    <div className="grid gap-4 md:grid-cols-2">
                                        {modules.map((module) => {
                                            const id = toInt(module.id);
                                            const active = selectedModules.includes(id);
                                            const moduleTotal = getModuleTotal(module);

                                            return (
                                                <button
                                                    key={id}
                                                    type="button"
                                                    onClick={() => toggleModule(id)}
                                                    className={cn(
                                                        'rounded-2xl border p-4 text-left transition',
                                                        active
                                                            ? 'border-[#00559b] bg-[#f1f8fd] shadow-sm'
                                                            : 'border-slate-200 bg-white hover:border-[#9dbfda]'
                                                    )}
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div className="space-y-1">
                                                            <p className="text-sm font-semibold text-[#0f172a]">{module.label}</p>
                                                            <p className="text-xs text-[#5f7182]">
                                                                {formatMoney(module.prix_mensuel ?? 0)} / mois
                                                            </p>
                                                        </div>
                                                        <div className="text-right">
                                                            <p className="text-sm font-semibold text-[#00559b]">
                                                                {formatMoney(moduleTotal)}
                                                            </p>
                                                            <p className="text-xs text-[#5f7182]">
                                                                pour {durationMonths} mois
                                                            </p>
                                                        </div>
                                                        <div
                                                            className={cn(
                                                                'flex h-6 w-6 items-center justify-center rounded-full border',
                                                                active
                                                                    ? 'border-[#00559b] bg-[#00559b] text-white'
                                                                    : 'border-slate-300 bg-white text-transparent'
                                                            )}
                                                        >
                                                            <Check className="h-3.5 w-3.5" />
                                                        </div>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-[#5f7182]">
                                        Aucun module additionnel disponible pour le moment.
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <aside className="space-y-6">
                        <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg text-[#0f172a]">Récapitulatif</CardTitle>
                                <CardDescription>Avant d’acceder au portail de paiement.</CardDescription>
                            </CardHeader>
                            <CardContent className="mt-4 space-y-4 p-6">
                                <SummaryLine label="Agence" value={agencyName} />
                                <SummaryLine label="Plan" value={plan.nom ?? 'Abonnement de base'} />
                                <SummaryLine label="Duree" value={`${duration} mois`} />
                                <SummaryLine label="Modules" value={`${selectedModules.length} selectionne(s)`} />
                                <Separator />
                                <SummaryLine label="Base" value={formatMoney(baseTotal)} />
                                <SummaryLine label="Modules" value={formatMoney(modulesTotal)} />
                                <SummaryLine label="Total" value={formatMoney(total)} strong />
                            </CardContent>
                        </Card>

                        <Card className="rounded-[1.5rem] border-[#d7e3ee] bg-[#f8fbfe] shadow-sm">
                            <CardContent className="mt-4 space-y-3 p-6">
                                <div className="flex items-start gap-3">
                                  
                                    <div>
                                        <p className="text-sm font-semibold text-[#0f172a]">Etape suivante</p>
                                        <p className="mt-1 text-sm leading-6 text-[#5f7182]">
                                            Proceder au paiement.
                                        </p>
                                    </div>
                                </div>
                                <Button
                                    type="button"
                                    onClick={submitCheckout}
                                    className="h-11 w-full rounded-2xl bg-[#00559b] font-semibold text-white hover:bg-[#00457c]"
                                >
                                    {subscriptionFlow.button_label ?? 'Continuer'}
                                </Button>
                            </CardContent>
                        </Card>
                    </aside>
                </section>
            </div>
        </AgenceLayout>
    );
}

function SummaryLine({ label, value, strong = false }) {
    return (
        <div className="flex items-center justify-between gap-3 text-sm">
            <span className="text-[#5f7182]">{label}</span>
            <span className={cn('text-right text-[#0f172a]', strong && 'font-semibold')}>{value}</span>
        </div>
    );
}

function getSubscriptionFlowBadgeLabel(state) {
    switch (state) {
        case 'new':
            return 'Nouvelle souscription';
        case 'expired':
            return 'Réactivation';
        case 'urgent_renewal':
            return 'Renouvellement urgent';
        case 'renewal':
            return 'Renouvellement anticipé';
        default:
            return 'Abonnement';
    }
}
