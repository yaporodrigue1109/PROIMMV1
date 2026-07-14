import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Layers3, PencilLine, Ticket } from 'lucide-react';

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
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';

const selectClassName =
    'flex h-11 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

const formatDate = (value) => {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return date.toISOString().slice(0, 10);
};

export default function Form({ mode, abonnement = null, plans = [] }) {
    const isEdit = mode === 'edit';

    const moduleCatalog = useMemo(() => {
        return Array.from(
            new Set(
                plans.flatMap((plan) => plan.modules ?? [
                    'Annonces limitees',
                    'Annonces standard',
                    'Annonces illimitees',
                    'SMS',
                    'WhatsApp',
                    'Statistiques',
                    'Support prioritaire',
                ])
            )
        );
    }, [plans]);

    const [form, setForm] = useState({
        agence: abonnement?.agence ?? '',
        code_agence: abonnement?.code_agence ?? '',
        plan: abonnement?.plan ?? plans?.[1]?.nom ?? 'Standard',
        montant: Number(abonnement?.montant ?? plans?.[1]?.prix ?? 25000),
        cycle: abonnement?.cycle ?? 'Mensuel',
        date_debut: formatDate(abonnement?.date_debut),
        date_fin: formatDate(abonnement?.date_fin),
        statut: abonnement?.statut ?? 'En attente',
        notes: abonnement?.notes ?? '',
        modules: abonnement?.modules ?? [],
    });

    const selectedPlan = plans.find((plan) => plan.nom === form.plan) ?? plans[0] ?? null;
    const modulesTotal = form.modules.length * 5000;
    const total = Number(form.montant ?? 0) + modulesTotal;

    const toggleModule = (module) => {
        setForm((current) => {
            const exists = current.modules.includes(module);
            return {
                ...current,
                modules: exists
                    ? current.modules.filter((item) => item !== module)
                    : [...current.modules, module],
            };
        });
    };

    return (
        <AdminLayout title={isEdit ? 'Modifier un abonnement' : 'Nouvel abonnement'}>
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-3">
                      
                            <div>
                                <h1 className="mt-4 text-3xl font-semibold tracking-tight text-slate-900">
                                    {isEdit ? 'Modifier un abonnement' : 'Creer un abonnement'}
                                </h1>
                               
                            </div>
                        </div>

                        <div className="mt-4 flex flex-wrap gap-3">
                            <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/abonnements">
                                    <ArrowLeft className="h-4 w-4" />
                                    Retour
                                </Link>
                            </Button>
                            {isEdit ? (
                                <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                    <Link href={`/admin/abonnements/${abonnement?.code_agence}`}>
                                        <PencilLine className="h-4 w-4" />
                                        Voir le detail
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="space-y-6">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Compte concerne</CardTitle>
                                
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 sm:grid-cols-2">
                                <Field label="Agence" className=" mt-4 sm:col-span-2">
                                    <Input
                                        value={form.agence}
                                        onChange={(event) => setForm({ ...form, agence: event.target.value })}
                                        placeholder="Pros Immobilier Cocody"
                                    />
                                </Field>

                                <Field label="Code agence" className=" mt-4 sm:col-span-2">
                                    <Input
                                        value={form.code_agence}
                                        onChange={(event) => setForm({ ...form, code_agence: event.target.value })}
                                        placeholder="AGC-001"
                                    />
                                </Field>

                                <Field label="Statut">
                                    <select
                                        value={form.statut}
                                        onChange={(event) => setForm({ ...form, statut: event.target.value })}
                                        className={selectClassName}
                                    >
                                        <option value="Actif">Actif</option>
                                        <option value="En attente">En attente</option>
                                        <option value="Expire">Expire</option>
                                        <option value="Suspendu">Suspendu</option>
                                    </select>
                                </Field>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Facturation</CardTitle>
                                <CardDescription>Choisissez le plan, le cycle et les dates de suivi.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 sm:grid-cols-2">
                                <Field label="Plan" className=" mt-4 sm:col-span-2">
                                    <select
                                        value={form.plan}
                                        onChange={(event) => setForm({ ...form, plan: event.target.value })}
                                        className={selectClassName}
                                    >
                                        {plans.map((plan) => (
                                            <option key={plan.nom} value={plan.nom}>
                                                {plan.nom}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Cycle" className=" mt-4 sm:col-span-2">
                                    <select
                                        value={form.cycle}
                                        onChange={(event) => setForm({ ...form, cycle: event.target.value })}
                                        className={selectClassName}
                                    >
                                        <option value="Mensuel">Mensuel</option>
                                        <option value="Trimestriel">Trimestriel</option>
                                        <option value="Semestriel">Semestriel</option>
                                        <option value="Annuel">Annuel</option>
                                    </select>
                                </Field>

                                <Field label="Montant">
                                    <Input
                                        type="number"
                                        value={form.montant}
                                        onChange={(event) => setForm({ ...form, montant: event.target.value })}
                                        placeholder="25000"
                                    />
                                </Field>

                                <Field label="Date de debut">
                                    <Input
                                        type="date"
                                        value={form.date_debut}
                                        onChange={(event) => setForm({ ...form, date_debut: event.target.value })}
                                    />
                                </Field>

                                <Field label="Date de fin">
                                    <Input
                                        type="date"
                                        value={form.date_fin}
                                        onChange={(event) => setForm({ ...form, date_fin: event.target.value })}
                                    />
                                </Field>

                                <Field label="Notes" className="sm:col-span-2">
                                    <textarea
                                        value={form.notes}
                                        onChange={(event) => setForm({ ...form, notes: event.target.value })}
                                        className={textareaClassName}
                                        placeholder="Commentaire interne"
                                    />
                                </Field>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Modules</CardTitle>
                                <CardDescription>Activez les options qui accompagnent ce plan.</CardDescription>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="grid gap-3 sm:grid-cols-2 mt-4">
                                    {moduleCatalog.map((module) => {
                                        const checked = form.modules.includes(module);

                                        return (
                                            <button
                                                key={module}
                                                type="button"
                                                onClick={() => toggleModule(module)}
                                                className={`flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition ${
                                                    checked
                                                        ? 'border-[#00559b] bg-blue-50'
                                                        : 'border-slate-200 bg-white hover:bg-slate-50'
                                                }`}
                                            >
                                                <span className="text-sm font-medium text-slate-900">{module}</span>
                                                <span
                                                    className={`inline-flex h-5 w-5 items-center justify-center rounded-full border text-xs font-bold ${
                                                        checked
                                                            ? 'border-[#00559b] bg-[#00559b] text-white'
                                                            : 'border-slate-300 text-transparent'
                                                    }`}
                                                >
                                                    ✓
                                                </span>
                                            </button>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Resume</CardTitle>
                                <CardDescription>Lecture rapide du montant et des options choisies.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p className="text-sm text-slate-500">Plan selectionne</p>
                                    <p className="mt-2 text-xl font-semibold text-slate-900">{selectedPlan?.nom ?? 'Plan inconnu'}</p>
                                    <p className="mt-2 text-sm leading-6 text-slate-600">
                                        {selectedPlan?.description ?? 'Aucune description disponible.'}
                                    </p>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-2">
                                    <SummaryMini label="Prix de base" value={formatMoney(Number(form.montant ?? 0))} />
                                    <SummaryMini label="Modules actifs" value={`${form.modules.length} selection(s)`} />
                                    <SummaryMini label="Supplement modules" value={formatMoney(modulesTotal)} />
                                    <SummaryMini label="Total estime" value={formatMoney(total)} tone="text-[#00559b]" />
                                </div>

                             

                                <Button type="button" disabled className="h-11 w-full rounded-xl bg-[#00559b] text-white opacity-70">
                                    Enregistrement a brancher
                                </Button>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Plan associe</CardTitle>
                                <CardDescription>Modules et tarifs issus de la configuration du plan.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6 mt-4">
                                {plans.map((plan) => (
                                    <button
                                        key={plan.nom}
                                        type="button"
                                        onClick={() => setForm({ ...form, plan: plan.nom, montant: plan.prix })}
                                        className={`w-full rounded-2xl border p-4 text-left transition ${
                                            form.plan === plan.nom
                                                ? 'border-[#00559b] bg-blue-50'
                                                : 'border-slate-200 bg-white hover:bg-slate-50'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between gap-3">
                                            <div>
                                                <p className="font-semibold text-slate-900">{plan.nom}</p>
                                                <p className="mt-1 text-sm text-slate-500">{plan.description}</p>
                                            </div>
                                            <Badge variant={plan.highlight ? 'secondary' : 'outline'} className="rounded-full">
                                                {formatMoney(plan.prix)}
                                            </Badge>
                                        </div>
                                    </button>
                                ))}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function Field({ label, className = '', children }) {
    return (
        <label className={`space-y-2 ${className}`}>
            <Label className="text-sm font-medium text-slate-700">{label}</Label>
            {children}
        </label>
    );
}

function SummaryMini({ label, value, tone = 'text-slate-900' }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{label}</p>
            <p className={`mt-2 text-base font-semibold ${tone}`}>{value}</p>
        </div>
    );
}
