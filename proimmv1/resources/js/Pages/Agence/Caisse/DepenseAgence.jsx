import { useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Banknote,
    CalendarDays,
    Check,
    Home,
    Loader2,
    Plus,
    ShoppingBag,
    Trash2,
    Wrench,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const shortcuts = [
    { title: 'Loyers', href: '/agence/caisse/loyer', icon: Home, active: false },
    { title: 'Maintenance', href: '/agence/caisse/maintenance', icon: Wrench, active: false },
    { title: 'Dépenses agence', href: '/agence/caisse/depense-agence', icon: ShoppingBag, active: true },
    { title: 'Vente de biens', href: '/agence/caisse/vente-bien', icon: Banknote, active: false },
];

const categories = [
    'Paiement facture',
    'Fournitures',
    'Transport',
    'Communication',
    'Achat matériel',
    'Divers',
];

const paymentModes = ['Espèces', 'Wave', 'Orange Money', 'Virement bancaire'];

const proofTypes = ['Aucun', 'Reçu', 'Facture', 'Bon de sortie'];

const blankExpense = () => ({
    category: '',
    label: '',
    amount: '',
    paymentMode: 'Espèces',
    proofType: 'Aucun',
    observation: '',
});

function makeId() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }

    return `dep-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function Field({ label, required, children, className }) {
    return (
        <label className={cn('space-y-1.5', className)}>
            <span className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </span>
            {children}
        </label>
    );
}

function SectionCard({ icon: Icon, title, description, children, action }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                        <Icon className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">{title}</CardTitle>
                        {description ? (
                            <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription>
                        ) : null}
                    </div>
                </div>
                {action}
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
    );
}

function ExpenseRow({ expense, onRemove }) {
    return (
        <div className="rounded-3xl border border-[#dbe7ee] bg-[#f8fafc] p-4">
            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <p className="truncate text-base font-semibold text-[#0f172a]">{expense.label}</p>
                    <p className="mt-1 text-sm text-[#5f7182]">{expense.reference}</p>
                </div>
                <strong className="shrink-0 text-[#b42318]">{currency(expense.amount)}</strong>
            </div>

            <div className="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                <div className="rounded-2xl bg-white px-3 py-2">
                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Catégorie</span>
                    <strong className="text-[#0f172a]">{expense.category}</strong>
                </div>
                <div className="rounded-2xl bg-white px-3 py-2">
                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Paiement</span>
                    <strong className="text-[#0f172a]">{expense.paymentMode}</strong>
                </div>
                <div className="rounded-2xl bg-white px-3 py-2">
                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Justificatif</span>
                    <strong className="text-[#0f172a]">{expense.proofType}</strong>
                </div>
            </div>

            {expense.observation ? (
                <p className="mt-3 rounded-2xl border border-[#e2e8f0] bg-white px-3 py-2 text-sm text-[#0f172a]">
                    {expense.observation}
                </p>
            ) : null}

            <div className="mt-4 flex justify-end">
                <button
                    type="button"
                    onClick={() => onRemove(expense.id)}
                    className="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-[#b42318] transition hover:bg-[#fdecec]"
                >
                    <Trash2 className="h-4 w-4" />
                    Supprimer
                </button>
            </div>
        </div>
    );
}

export default function DepenseAgence({ caisseOuverte = true }) {
    const [expenseForm, setExpenseForm] = useState(blankExpense());
    const [expenses, setExpenses] = useState([]);
    const [referenceCounter, setReferenceCounter] = useState(1);
    const [submitting, setSubmitting] = useState(false);
    const [flash, setFlash] = useState(null);

    const reference = `DEP-AG-2026-${String(referenceCounter).padStart(4, '0')}`;
    const total = useMemo(() => expenses.reduce((sum, item) => sum + Number(item.amount ?? 0), 0), [expenses]);

    const resetExpenseForm = () => {
        setExpenseForm(blankExpense());
        setFlash(null);
    };

    const addExpense = () => {
        if (!expenseForm.category) {
            setFlash({ type: 'error', message: 'Veuillez sélectionner une catégorie.' });
            return;
        }

        if (!expenseForm.label.trim()) {
            setFlash({ type: 'error', message: 'Veuillez renseigner le libellé.' });
            return;
        }

        const amount = Number(expenseForm.amount || 0);
        if (amount <= 0) {
            setFlash({ type: 'error', message: 'Veuillez renseigner un montant valide.' });
            return;
        }

        setExpenses((current) => [
            ...current,
            {
                id: makeId(),
                reference,
                category: expenseForm.category,
                label: expenseForm.label.trim(),
                amount,
                paymentMode: expenseForm.paymentMode,
                proofType: expenseForm.proofType,
                observation: expenseForm.observation.trim(),
            },
        ]);

        setReferenceCounter((current) => current + 1);
        setExpenseForm(blankExpense());
        setFlash({ type: 'success', message: 'Dépense ajoutée au brouillon.' });
    };

    const removeExpense = (id) => {
        setExpenses((current) => current.filter((item) => item.id !== id));
    };

    const clearExpenses = () => {
        setExpenses([]);
        setReferenceCounter(1);
        setExpenseForm(blankExpense());
        setFlash({ type: 'info', message: 'La liste des dépenses a été réinitialisée.' });
    };

    const validateExpenses = async () => {
        if (!expenses.length) return;

        if (typeof window !== 'undefined' && !window.confirm(`Valider le décaissement global de ${currency(total)} ?`)) {
            return;
        }

        setSubmitting(true);

        try {
            await new Promise((resolve) => setTimeout(resolve, 500));
            if (typeof window !== 'undefined') {
                window.alert('Dépenses validées en statique.');
            }
            clearExpenses();
            setFlash({ type: 'success', message: 'Dépenses validées en statique.' });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <AgenceLayout title="Dépense agence">
            <Head title="Dépense agence" />

            <div className="mx-auto flex max-w-6xl flex-col gap-6 pb-10">
                <div className="flex items-center gap-3">
                    <Button asChild variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/caisse">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-xl font-semibold text-[#0f172a]">Dépense agence</h2>
                           
                        </div>
                       
                    </div>
                </div>

                <div className="flex flex-wrap justify-center gap-2">
                    {shortcuts.map((shortcut) => {
                        const Icon = shortcut.icon;

                        return (
                            <Link
                                key={shortcut.title}
                                href={shortcut.href}
                                className={cn(
                                    'flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-medium transition',
                                    shortcut.active
                                        ? 'border-[#00559b] bg-[#00559b] text-white'
                                        : 'border-[#c8d4de] bg-white text-[#0f172a] hover:border-[#00559b] hover:text-[#00559b]'
                                )}
                            >
                                <Icon className="h-4 w-4" />
                                {shortcut.title}
                            </Link>
                        );
                    })}
                </div>

                {flash ? (
                    <div
                        className={cn(
                            'rounded-xl px-4 py-3 text-sm',
                            flash.type === 'success'
                                ? 'border border-[#c8e6c9] bg-[#eef8df] text-[#245b00]'
                                : flash.type === 'error'
                                    ? 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                                    : 'border border-[#dbe7ee] bg-[#f8fafc] text-[#5f7182]'
                        )}
                    >
                        {flash.message}
                    </div>
                ) : null}

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-[3fr_1fr]">
                    <SectionCard
                        icon={ShoppingBag}
                        title="Nouvelle dépense agence"
                        description="Complétez le formulaire puis ajoutez la dépense au brouillon."
                        action={<Badge variant="secondary" className="rounded-full px-3 py-1 text-[11px]">Brouillon</Badge>}
                    >
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field label="Catégorie" required>
                                <Select
                                    value={expenseForm.category}
                                    onValueChange={(value) => setExpenseForm((current) => ({ ...current, category: value }))}
                                >
                                    <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                        <SelectValue placeholder="Sélectionner une catégorie" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categories.map((category) => (
                                            <SelectItem key={category} value={category}>
                                                {category}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Référence">
                                <Input value={reference} disabled className="h-11 rounded-xl border-[#c8d4de]" />
                            </Field>

                            <Field label="Libellé" required>
                                <Input
                                    value={expenseForm.label}
                                    onChange={(event) => setExpenseForm((current) => ({ ...current, label: event.target.value }))}
                                    placeholder="Ex. Achat ramettes papier A4"
                                    className="h-11 rounded-xl border-[#c8d4de]"
                                />
                            </Field>

                            <Field label="Montant" required>
                                <Input
                                    type="number"
                                    min="0"
                                    value={expenseForm.amount}
                                    onChange={(event) => setExpenseForm((current) => ({ ...current, amount: event.target.value }))}
                                    placeholder="Ex. 4500"
                                    className="h-11 rounded-xl border-[#c8d4de]"
                                />
                            </Field>

                            <Field label="Mode de paiement">
                                <Select
                                    value={expenseForm.paymentMode}
                                    onValueChange={(value) => setExpenseForm((current) => ({ ...current, paymentMode: value }))}
                                >
                                    <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {paymentModes.map((mode) => (
                                            <SelectItem key={mode} value={mode}>
                                                {mode}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Justificatif">
                                <Select
                                    value={expenseForm.proofType}
                                    onValueChange={(value) => setExpenseForm((current) => ({ ...current, proofType: value }))}
                                >
                                    <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {proofTypes.map((proof) => (
                                            <SelectItem key={proof} value={proof}>
                                                {proof}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Observation" className="md:col-span-2">
                                <textarea
                                    value={expenseForm.observation}
                                    onChange={(event) => setExpenseForm((current) => ({ ...current, observation: event.target.value }))}
                                    placeholder="Observation facultative..."
                                    className="min-h-[110px] w-full rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b] focus:ring-2 focus:ring-[#00559b]/20"
                                />
                            </Field>
                        </div>

                        <div className="mt-5 flex flex-col gap-3 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-center gap-3 text-sm text-[#5f7182]">
                                <CalendarDays className="h-4 w-4 text-[#00559b]" />
                                <span>Les dépenses sont ajoutées au brouillon avant validation finale.</span>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={resetExpenseForm}>
                                    Réinitialiser
                                </Button>
                                <Button type="button" className={agenceButtonStyles.primary} onClick={addExpense}>
                                    <Plus className="h-4 w-4" />
                                    Ajouter la dépense
                                </Button>
                            </div>
                        </div>
                    </SectionCard>

                    <div className="lg:sticky lg:top-4 self-start">
                        <SectionCard
                            icon={Banknote}
                            title="Dépenses à payer"
                            description={expenses.length ? `${expenses.length} dépense(s) ajoutée(s)` : 'Aucune dépense ajoutée'}
                        >
                            <div className="mb-4 flex items-center justify-between rounded-2xl bg-[#f8fafc] px-4 py-3">
                                <span className="text-sm text-[#5f7182]">Total à valider</span>
                                <strong className="text-lg text-[#b42318]">{currency(total)}</strong>
                            </div>

                            {expenses.length ? (
                                <div className="space-y-3">
                                    {expenses.map((expense) => (
                                        <ExpenseRow key={expense.id} expense={expense} onRemove={removeExpense} />
                                    ))}
                                </div>
                            ) : (
                                <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center text-sm text-[#5f7182]">
                                    Ajoutez une ou plusieurs dépenses avant de valider le décaissement.
                                </div>
                            )}

                            <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={clearExpenses}>
                                    Tout vider
                                </Button>

                                <Button type="button" className={agenceButtonStyles.primary} disabled={!expenses.length || submitting} onClick={validateExpenses}>
                                    {submitting ? (
                                        <>
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            Validation...
                                        </>
                                    ) : (
                                        <>
                                            <Check className="h-4 w-4" />
                                            Valider
                                        </>
                                    )}
                                </Button>
                            </div>
                        </SectionCard>
                    </div>
                </div>
            </div>
        </AgenceLayout>
    );
}
