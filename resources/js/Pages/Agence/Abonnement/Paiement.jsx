import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Banknote, Check, CreditCard } from 'lucide-react';
import { useState } from 'react';

import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { cn } from '../../../lib/utils';

const formatMoney = (value) =>
    `${new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))} FCFA`;

function SummaryLine({ label, value, strong = false }) {
    return (
        <div className="flex items-center justify-between gap-3 text-sm">
            <span className="text-[#5f7182]">{label}</span>
            <span className={cn('text-right text-[#0f172a]', strong && 'font-semibold')}>{value}</span>
        </div>
    );
}

export default function Paiement({ draft = {}, tarifs = {} }) {
    const page = usePage();
    const agency = page?.props?.auth?.user?.agence ?? null;
    const subscriptionContext = draft.subscription_context ?? {};
    const modules = Array.isArray(draft.modules) ? draft.modules : [];
    const [selectedMethod, setSelectedMethod] = useState('orange_money');
    const plan = tarifs.plan ?? {};
    const agencyName = agency?.name ?? agency?.nom ?? agency?.raison_sociale ?? agency?.nom_agence ?? 'Mon agence';
    const pageTitle = subscriptionContext.title ?? 'Portail de paiement';
    const duration = Number(draft.duree_mois ?? 0);
    const baseValue =
        Number(draft.prix_base ?? draft.prix_base_ht ?? 0) > 0
            ? Number(draft.prix_base ?? draft.prix_base_ht ?? 0)
            : Number(plan.prix_mensuel ?? 0) * duration;
    const modulesValue = Number(draft.prix_modules ?? 0);
    const totalValue = Number(draft.prix_total ?? draft.total ?? baseValue + modulesValue);
    const submitTestValidation = () => {
        const chosenMethod = paymentMethods.find((method) => method.value === selectedMethod);

        router.post('/agence/abonnement/paiement/test', {
            mode_paiement: chosenMethod?.code ?? 'autre',
        });
    };

    const paymentMethods = [
        {
            name: 'Orange Money',
            value: 'orange_money',
            code: 'mobile_money',
            image: '/admin/assets/images/paiement/orange.png',
            note: 'Paiement mobile sécurisé',
        },
        {
            name: 'Wave',
            value: 'wave',
            code: 'mobile_money',
            image: '/admin/assets/images/paiement/wave.png',
            note: 'Paiement mobile sécurisé',
        },
        {
            name: 'Carte bancaire',
            value: 'carte_bancaire',
            code: 'carte',
            icon: CreditCard,
            note: 'Paiement CB / Visa / Mastercard',
        },
        {
            name: 'Virement bancaire',
            value: 'virement_bancaire',
            code: 'virement',
            icon: Banknote,
            note: 'Pour les règlements par banque',
        },
    ];

    return (
        <AgenceLayout title={pageTitle}>
            <Head title={pageTitle} />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 pb-10">
                <div className="flex items-center gap-3">
                    <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/abonnement">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">{pageTitle}</h1>
                        <p className="mt-1 text-sm text-[#5f7182]">
                            {subscriptionContext.description ?? 'Selectionnez la methode de paiement puis validez votre demande.'}
                        </p>
                    </div>
                </div>

                <section className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                    <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg text-[#0f172a]">Moyens de paiement</CardTitle>
                            <CardDescription>Selectionnez la methode qui vous convient le mieux.</CardDescription>
                        </CardHeader>
                        <CardContent className="mt-4 p-6">
                            <div className="grid gap-4 md:grid-cols-2">
                                {paymentMethods.map((method) => {
                                    const Icon = method.icon;
                                    const active = selectedMethod === method.value;

                                    return (
                                        <button
                                            key={method.name}
                                            type="button"
                                            onClick={() => setSelectedMethod(method.value)}
                                            aria-pressed={active}
                                            className={cn(
                                                'rounded-2xl border p-4 text-left shadow-sm transition',
                                                active
                                                    ? 'border-[#00559b] bg-[#eef7fd] ring-2 ring-[#00559b]/10'
                                                    : 'border-slate-200 bg-white hover:border-[#9dbfda]'
                                            )}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className={cn(
                                                        'flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl',
                                                        active ? 'bg-[#00559b]/10' : 'bg-[#f8fafc]'
                                                    )}
                                                >
                                                    {method.image ? (
                                                        <img
                                                            src={method.image}
                                                            alt={method.name}
                                                            className="h-full w-full object-contain p-2"
                                                        />
                                                    ) : (
                                                        <Icon className="h-5 w-5 text-[#00559b]" />
                                                    )}
                                                </div>
                                                <div className="flex-1">
                                                    <p className="text-sm font-semibold text-[#0f172a]">{method.name}</p>
                                                    <p className="mt-1 text-sm leading-6 text-[#5f7182]">{method.note}</p>
                                                </div>
                                                {active ? (
                                                    <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#00559b] text-white">
                                                        <Check className="h-3.5 w-3.5" />
                                                    </div>
                                                ) : null}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg text-[#0f172a]">Recapitulatif</CardTitle>
                            <CardDescription>Votre selection avant paiement.</CardDescription>
                        </CardHeader>
                        <CardContent className="mt-4 space-y-4 p-6">
                            <SummaryLine label="Agence" value={agencyName} />
                            <SummaryLine label="Plan" value={plan.nom ?? 'Abonnement de base'} />
                            <SummaryLine label="Duree" value={`${duration} mois`} />
                            <SummaryLine label="Modules" value={`${modules.length} selectionne(s)`} />
                            <Separator />
                            <SummaryLine label="Base" value={formatMoney(baseValue)} />
                            <SummaryLine label="Modules" value={formatMoney(modulesValue)} />
                            <SummaryLine label="Total" value={formatMoney(totalValue)} strong />
                            <div className="space-y-2">
                                {modules.length ? (
                                    modules.map((module) => (
                                        <div
                                            key={module.id}
                                            className="flex items-center justify-between gap-3 rounded-xl bg-[#f8fafc] px-3 py-2 text-sm"
                                        >
                                            <span className="text-[#0f172a]">{module.label}</span>
                                            <span className="font-medium text-[#00559b]">
                                                {formatMoney(Number(module.prix_total ?? Number(module.prix_mensuel ?? 0) * Number(draft.duree_mois ?? 0)))}
                                            </span>
                                        </div>
                                    ))
                                ) : (
                                    <div className="rounded-xl bg-[#f8fafc] px-3 py-2 text-sm text-[#5f7182]">
                                        Aucun module additionnel.
                                    </div>
                                )}
                            </div>
                            <Button
                                type="button"
                                onClick={submitTestValidation}
                                className="h-11 w-full rounded-2xl bg-emerald-600 font-semibold text-white hover:bg-emerald-700"
                            >
                                {subscriptionContext.button_label ?? 'Valider test'}
                            </Button>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </AgenceLayout>
    );
}

function InfoTile({ label, value }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <p className="text-xs uppercase tracking-[0.22em] text-[#5f7182]">{label}</p>
            <p className="mt-2 text-sm font-semibold text-[#0f172a]">{value}</p>
        </div>
    );
}
