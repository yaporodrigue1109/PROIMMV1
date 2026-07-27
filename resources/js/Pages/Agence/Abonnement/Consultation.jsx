import { Head, Link, router } from '@inertiajs/react';
import {
    CalendarDays,
    CircleDollarSign,
    Clock3,
    Download,
    RefreshCcw,
    ShieldCheck,
} from 'lucide-react';

import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { cn } from '../../../lib/utils';

const formatMoney = (value) =>
    `${new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))} FCFA`;

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('fr-FR');
};

const paymentVariant = {
    validee: 'success',
    en_attente: 'warning',
    echouee: 'destructive',
    annulee: 'secondary',
    remboursee: 'outline',
};

export default function Consultation({ consultation = {} }) {
    const plan = consultation.plan ?? {};
    const payments = Array.isArray(consultation.payments?.data) ? consultation.payments.data : [];
    const pagination = consultation.payments ?? {};
    const primaryAction = consultation.primary_action ?? {};

    return (
        <AgenceLayout title="Abonnement et paiements">
            <Head title="Abonnement et paiements" />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 pb-10">
                {consultation.renewal_alert ? (
                    <Card
                        className={cn(
                            'rounded-[1.5rem] border shadow-sm',
                            consultation.renewal_alert.tone === 'danger'
                                ? 'border-rose-200 bg-rose-50'
                                : 'border-amber-200 bg-amber-50'
                        )}
                    >
                        <CardContent className="mt-4 flex flex-col gap-4 p-5 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p
                                    className={cn(
                                        'text-sm font-semibold',
                                        consultation.renewal_alert.tone === 'danger'
                                            ? 'text-rose-900'
                                            : 'text-amber-900'
                                    )}
                                >
                                    {consultation.renewal_alert.title}
                                </p>
                                <p
                                    className={cn(
                                        'mt-1 text-sm leading-6',
                                        consultation.renewal_alert.tone === 'danger'
                                            ? 'text-rose-800'
                                            : 'text-amber-800'
                                    )}
                                >
                                    {consultation.renewal_alert.message}
                                </p>
                            </div>

                            <Button asChild className="h-11 rounded-xl bg-[#00559b] font-semibold text-white hover:bg-[#00457c]">
                                <Link href={primaryAction.href ?? consultation.renew_url ?? '/agence/abonnement?renew=1'}>
                                    <RefreshCcw className="mr-2 h-4 w-4" />
                                    {primaryAction.button_label ?? 'Renouveler'}
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : null}

                <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                    <CardHeader className="flex flex-col gap-4 border-b border-slate-200 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle className="text-lg text-[#0f172a]">Abonnement actuel</CardTitle>
                            <CardDescription>Informations principales de votre formule.</CardDescription>
                        </div>

                        <Button asChild className="h-11 rounded-xl bg-[#00559b] font-semibold text-white hover:bg-[#00457c]">
                            <Link href={primaryAction.href ?? consultation.renew_url ?? '/agence/abonnement?renew=1'}>
                                <RefreshCcw className="mr-2 h-4 w-4" />
                                {primaryAction.button_label ?? 'Renouveler'}
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent className="mt-4 space-y-5 p-6">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <InfoTile icon={ShieldCheck} label="Plan" value={plan.name ?? 'Aucun abonnement'} />
                            <InfoTile icon={CalendarDays} label="Début" value={formatDate(plan.start)} />
                            <InfoTile icon={CalendarDays} label="Fin" value={formatDate(plan.end)} />
                            <InfoTile
                                icon={Clock3}
                                label="Jours restants"
                                value={plan.days_remaining != null ? `${plan.days_remaining} jour(s)` : '—'}
                            />
                            <InfoTile
                                icon={CircleDollarSign}
                                label="Montant"
                                value={formatMoney(plan.current_amount ?? plan.amount_monthly ?? 0)}
                            />
                        </div>

                        <div className="rounded-2xl border border-slate-200 bg-white p-5">
                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                Modules inclus
                            </p>
                            <div className="mt-3 flex flex-wrap gap-2">
                                {(plan.modules ?? []).length ? (
                                    plan.modules.map((module) => (
                                        <Badge key={module} variant="secondary" className="rounded-full px-3 py-1">
                                            {module}
                                        </Badge>
                                    ))
                                ) : (
                                    <span className="text-sm text-slate-500">
                                        Aucun module n&apos;est associé à cet abonnement.
                                    </span>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                    <CardHeader className="border-b border-slate-200">
                        <CardTitle className="text-lg text-[#0f172a]">Historique des paiements</CardTitle>
                        <CardDescription>
                            Chaque renouvellement et chaque validation de souscription.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-hidden rounded-b-[1.5rem]">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">Période</th>
                                        <th className="px-6 py-4 font-medium">Montant</th>
                                        <th className="px-6 py-4 font-medium">Paiement</th>
                                        <th className="px-6 py-4 font-medium">Mode</th>
                                        <th className="px-6 py-4 font-medium">Détail</th>
                                        <th className="px-6 py-4 font-medium">Référence</th>
                                        <th className="px-6 py-4 font-medium">Reçu</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200">
                                    {payments.length ? (
                                        payments.map((entry) => (
                                            <tr key={entry.id}>
                                                <td className="px-6 py-4 text-slate-900">
                                                    <div className="font-medium">{entry.period_label}</div>
                                                 
                                                </td>
                                                <td className="px-6 py-4 font-medium text-slate-900">
                                                    {formatMoney(entry.amount)}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <Badge
                                                        variant={paymentVariant[entry.status] ?? 'secondary'}
                                                        className="rounded-full"
                                                    >
                                                        {entry.status_label ?? entry.status}
                                                    </Badge>
                                                    <div className="mt-1 text-xs text-slate-500">
                                                        {entry.paid_at ? formatDate(entry.paid_at) : '—'}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-slate-700">
                                                    {entry.mode_label ?? entry.mode_paiement ?? '—'}
                                                </td>
                                                <td className="px-6 py-4 text-slate-700">
                                                    {entry.payment_detail ?? '—'}
                                                </td>
                                                <td className="px-6 py-4 text-slate-700">
                                                    {entry.reference ?? '—'}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {entry.receipt_url ? (
                                                        <Button asChild variant="outline" size="sm" className="rounded-xl border-[#c8d4de]">
                                                            <a href={entry.receipt_url}>
                                                                <Download className="mr-2 h-4 w-4" />
                                                                PDF
                                                            </a>
                                                        </Button>
                                                    ) : (
                                                        <span className="text-slate-400">—</span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="7" className="px-6 py-10 text-center text-slate-500">
                                                Aucun historique de paiement disponible pour le moment.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        <div className="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-slate-500">
                                Affichage de {pagination.from ?? 0} à {pagination.to ?? 0} sur {pagination.total ?? 0} paiements
                            </p>

                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="rounded-xl border-[#c8d4de]"
                                    disabled={!pagination.prev_page_url}
                                    onClick={() =>
                                        pagination.prev_page_url &&
                                        router.get(pagination.prev_page_url, {}, { preserveScroll: true, replace: true })
                                    }
                                >
                                    Précédent
                                </Button>

                                <span className="text-sm text-slate-500">
                                    Page {pagination.current_page ?? 1} / {pagination.last_page ?? 1}
                                </span>

                                <Button
                                    type="button"
                                    variant="outline"
                                    className="rounded-xl border-[#c8d4de]"
                                    disabled={!pagination.next_page_url}
                                    onClick={() =>
                                        pagination.next_page_url &&
                                        router.get(pagination.next_page_url, {}, { preserveScroll: true, replace: true })
                                    }
                                >
                                    Suivant
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {consultation.support?.phone || consultation.support?.email ? (
                    <Card className="rounded-[1.5rem] border-[#d7e3ee] shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-lg text-[#0f172a]">Support</CardTitle>
                            <CardDescription>En cas de litige ou de question sur un paiement.</CardDescription>
                        </CardHeader>
                        <CardContent className="mt-4 grid gap-3 p-6 md:grid-cols-2">
                            {consultation.support?.phone ? (
                                <Button asChild variant="outline" className="h-11 rounded-xl border-[#c8d4de]">
                                    <a href={`tel:${consultation.support.phone}`}>Contacter par téléphone</a>
                                </Button>
                            ) : null}
                            {consultation.support?.email ? (
                                <Button asChild variant="outline" className="h-11 rounded-xl border-[#c8d4de]">
                                    <a href={`mailto:${consultation.support.email}`}>Contacter par email</a>
                                </Button>
                            ) : null}
                        </CardContent>
                    </Card>
                ) : null}
            </div>
        </AgenceLayout>
    );
}

function InfoTile({ icon: Icon, label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                <Icon className="h-3.5 w-3.5" />
                {label}
            </div>
            <p className="mt-3 text-sm font-semibold text-[#0f172a]">{value}</p>
        </div>
    );
}
