import { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import {
    Building2,
    CalendarDays,
    Check,
    CheckCircle2,
    ChevronRight,
    ChevronDown,
    CircleDollarSign,
    Search,
    Sparkles,
    Ticket,
    X,
} from 'lucide-react';

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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../../../components/ui/select';
import { Switch } from '../../../components/ui/switch';
import { Textarea } from '../../../components/ui/textarea';
import { ComboboxField as SharedComboboxField } from '../../../components/ui/combobox-field';

const FALLBACK_DURATIONS = [1, 3, 6, 12, 24, 36];

const cardFieldClassName =
    'rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const asList = (value) => {
    if (Array.isArray(value)) {
        return value;
    }

    if (!value) {
        return [];
    }

    if (Array.isArray(value.data)) {
        return value.data;
    }

    if (typeof value.toArray === 'function') {
        return value.toArray();
    }

    if (typeof value[Symbol.iterator] === 'function') {
        return Array.from(value);
    }

    return [];
};

const toDateInputValue = (value) => {
    if (!value) return '';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
};

const todayValue = toDateInputValue(new Date());

const formatDate = (value) => {
    if (!value) return 'Non defini';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString('fr-FR');
};

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(
        Number(value ?? 0)
    ) + ' FCFA';

const normalizeAgency = (agency = {}) => ({
    id: agency?.agence_id ?? agency?.id ?? '',
    code: agency?.code_agence ?? agency?.code ?? '',
    name:
        agency?.name ??
        agency?.agence ??
        agency?.raison_sociale ??
        agency?.denomination ??
        'Agence',
    start: toDateInputValue(agency?.abonnement_start),
    end: toDateInputValue(agency?.abonnement_end),
    duration: Number(agency?.duree_mois ?? 12),
    baseTotal: Number(agency?.montant_base_total ?? 0),
    total: Number(agency?.montant_total ?? 0),
    notes: agency?.abonnement_notes ?? '',
    options: Array.isArray(agency?.options)
        ? agency.options
        : Array.isArray(agency?.modules_ids)
            ? agency.modules_ids
            : [],
});

const normalizeAgences = (agences) => {
    const list = Array.isArray(agences?.data)
        ? agences.data
        : asList(agences);

    return list.map((agency) => normalizeAgency(agency));
};

const normalizePlan = (tarifs = {}) => {
    const plan = tarifs?.plan ?? tarifs ?? {};

    return {
        label:
            plan?.nom ??
            plan?.label ??
            tarifs?.plan_nom ??
            'Plan unique',
        description:
            plan?.description ??
            tarifs?.plan_description ??
            'Abonnement standard de l agence.',
        monthlyPrice: Number(
            plan?.prix_mensuel ?? plan?.prix ?? tarifs?.plan_prix_mensuel ?? 0
        ),
        cycle:
            plan?.cycle ??
            tarifs?.cycle_facturation ??
            tarifs?.cycle ??
            'mois',
    };
};

const normalizeDurations = (tarifs = {}, monthlyPrice = 0) => {
    const source = Array.isArray(tarifs?.durees)
        ? tarifs.durees
        : asList(tarifs?.durees ?? tarifs?.plan?.durees);

    const rawList = source.length ? source : FALLBACK_DURATIONS;

    return rawList
        .map((item, index) => {
            if (typeof item === 'number' || typeof item === 'string') {
                const months = Number(item);
                if (!months) return null;

                return {
                    key: `duration-${months}`,
                    months,
                    label: `${months} mois`,
                    monthlyPrice,
                    totalPrice: monthlyPrice * months,
                };
            }

            const months = Number(
                item?.nombre_mois ??
                    item?.months ??
                    item?.mois ??
                    item?.value ??
                    item?.duree_mois ??
                    item?.duree ??
                    0
            );

            if (!months) return null;

            const monthly = Number(
                item?.prix_mensuel ?? item?.price ?? item?.cout_mensuel ?? monthlyPrice
            );
            const total = Number(
                item?.prix_total ?? item?.total ?? monthly * months
            );

            return {
                key: String(item?.id ?? item?.duree_id ?? index),
                months,
                label:
                    item?.label ??
                    item?.name ??
                    item?.libelle ??
                    `${months} mois`,
                monthlyPrice: monthly,
                totalPrice: total,
            };
        })
        .filter(Boolean);
};

const normalizeModules = (tarifs = {}) => {
    const source = Array.isArray(tarifs?.modules)
        ? tarifs.modules
        : asList(tarifs?.modules ?? tarifs?.plan?.modules);

    return source.map((item, index) => {
        if (typeof item === 'string') {
            return {
                id: index + 1,
                key: String(index + 1),
                label: item,
                description: '',
                monthlyPrice: 0,
                totalPrice: 0,
            };
        }

        const id = Number(item?.id ?? item?.module_id ?? item?.value ?? index + 1);

        return {
            id,
            key: String(id),
            label:
                item?.label ??
                item?.name ??
                item?.nom ??
                item?.libelle ??
                `Module ${id}`,
            description: item?.description ?? item?.details ?? '',
            monthlyPrice: Number(
                item?.prix_mensuel ?? item?.price ?? item?.cout_mensuel ?? 0
            ),
            totalPrice: Number(item?.prix_total ?? item?.total ?? 0),
        };
    });
};

const normalizeSelectedOptions = (options = []) =>
    Array.isArray(options)
        ? options
              .map((option) => {
                  if (option && typeof option === 'object') {
                      return Number(
                          option.id ??
                              option.module_id ??
                              option.value ??
                              option.option_id ??
                              0
                      );
                  }

                  const value = Number(option);
                  return Number.isNaN(value) ? option : value;
              })
              .filter((option) => option !== 0 && option !== '')
        : [];

const addMonthsToDate = (dateValue, months) => {
    if (!dateValue || !months) return '';

    const date = new Date(`${dateValue}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '';

    const next = new Date(date);
    next.setMonth(next.getMonth() + Number(months));

    const local = new Date(next.getTime() - next.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
};

const getAgencyLabel = (agency) => {
    if (!agency) return 'Aucune agence selectionnee';

    const code = agency.code ? ` ${agency.code}` : '';
    return `${agency.name}${code}`;
};

export default function Form({
    mode,
    agence = null,
    agences = [],
    tarifs = {},
}) {
    const isEdit = mode === 'edit';
    const isRenewal =
        typeof window !== 'undefined' &&
        new URLSearchParams(window.location.search).get('renew') === '1';

    const plan = useMemo(() => normalizePlan(tarifs), [tarifs]);
    const durationOptions = useMemo(
        () => normalizeDurations(tarifs, plan.monthlyPrice),
        [tarifs, plan.monthlyPrice]
    );
    const moduleOptions = useMemo(() => normalizeModules(tarifs), [tarifs]);
    const agencyOptions = useMemo(() => normalizeAgences(agences), [agences]);

    const initialAgency = normalizeAgency(agence ?? {});
    const initialDuration =
        Number(initialAgency.duration) ||
        durationOptions[0]?.months ||
        12;
    const initialStart = isRenewal
        ? todayValue
        : initialAgency.start ?? todayValue;
    const initialEnd = isRenewal
        ? addMonthsToDate(initialStart, initialDuration)
        : initialAgency.end ?? '';

    const form = useForm({
        agence_id: initialAgency.id ?? '',
        abonnement_start: initialStart,
        abonnement_end: initialEnd,
        duree_mois: initialDuration,
        montant_base_total: initialAgency.baseTotal ?? 0,
        montant_total: initialAgency.total ?? 0,
        options: normalizeSelectedOptions(initialAgency.options),
        abonnement_notes: initialAgency.notes ?? '',
    });

    const selectedAgency = isEdit
        ? initialAgency
        : agencyOptions.find(
              (item) => String(item.id) === String(form.data.agence_id)
          ) ?? null;

    const selectedDuration = useMemo(
        () =>
            durationOptions.find(
                (item) => String(item.months) === String(form.data.duree_mois)
            ) ?? durationOptions[0] ?? null,
        [durationOptions, form.data.duree_mois]
    );

    const selectedModuleOptions = useMemo(() => {
        const selected = new Set(
            (form.data.options ?? []).map((option) => String(option))
        );

        return moduleOptions.filter((module) => selected.has(String(module.id)));
    }, [moduleOptions, form.data.options]);

    const baseTotal = selectedDuration?.totalPrice ??
        plan.monthlyPrice * Number(form.data.duree_mois ?? 0);

    const modulesTotal = selectedModuleOptions.reduce((sum, module) => {
        const moduleTotal =
            module.totalPrice > 0
                ? module.totalPrice
                : module.monthlyPrice * Number(form.data.duree_mois ?? 0);

        return sum + moduleTotal;
    }, 0);

    const total = Number(baseTotal ?? 0) + Number(modulesTotal ?? 0);
    const computedEndDate = addMonthsToDate(
        form.data.abonnement_start,
        form.data.duree_mois
    );

    useEffect(() => {
        if (Number(form.data.montant_base_total) !== Number(baseTotal ?? 0)) {
            form.setData('montant_base_total', Number(baseTotal ?? 0));
        }

        if (Number(form.data.montant_total) !== Number(total ?? 0)) {
            form.setData('montant_total', Number(total ?? 0));
        }

        if (form.data.abonnement_end !== computedEndDate) {
            form.setData('abonnement_end', computedEndDate);
        }
    }, [baseTotal, computedEndDate, form, total]);

    useEffect(() => {
        if (!isEdit && !form.data.agence_id && agencyOptions.length === 1) {
            form.setData('agence_id', agencyOptions[0].id);
        }
    }, [agencyOptions, form, isEdit]);

    const submit = (event) => {
        event.preventDefault();

        const url = isEdit
            ? `/admin/abonnements/${agence?.code_agence}`
            : '/admin/abonnements';

        const method = isEdit ? 'put' : 'post';

        form[method](url, {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    const pageTitle = isRenewal
        ? 'Renouveler un abonnement'
        : isEdit
            ? 'Modifier un abonnement'
            : 'Abonner une agence';

    const actionLabel = form.processing
        ? 'Enregistrement...'
        : isRenewal
            ? 'Renouveler l\'abonnement'
            : isEdit
                ? 'Mettre à jour'
                : 'Abonner l\'agence';

    return (
        <AdminLayout
            title={pageTitle}
        >
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="mt-4 space-y-3">
                           
                            <div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
                                    {pageTitle}
                                </h1>
                                {isRenewal ? (
                                    <p className="mt-2 text-sm text-slate-500">
                                        Le renouvellement repart d&apos;aujourd&apos;hui et recalcule automatiquement la date de fin.
                                    </p>
                                ) : null}
                              
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button
                                asChild
                                variant="outline"
                                className="mt-4 h-11 rounded-xl border-slate-200 px-4 text-slate-900"
                            >
                                <Link href="/admin/abonnements">
                                    Retour
                                </Link>
                            </Button>

                            {isEdit && agence?.agence_id ? (
                                <Button
                                    asChild
                                    variant="outline"
                                    className="mt-4 h-11 rounded-xl border-slate-200 px-4 text-slate-900"
                                >
                                    <Link href={`/admin/agences/${agence?.code_agence}`}>
                                        Voir l'agence
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    </CardContent>
                </Card>

                <form onSubmit={submit} className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div className="space-y-6">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Agence cible</CardTitle>
                                <CardDescription>
                                    Choisis l'agence à abonner.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="mt-3 space-y-4 p-6">
                                {isEdit ? (
                                    <div className={cardFieldClassName}>
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                                    Agence selectionnée
                                                </p>
                                                <p className="mt-2 text-lg font-semibold text-slate-900">
                                                    {getAgencyLabel(selectedAgency)}
                                                </p>
                                            </div>
                                        
                                        </div>
                                        <input
                                            type="hidden"
                                            value={form.data.agence_id}
                                            onChange={() => {}}
                                        />
                                    </div>
                                ) : (
                                    <SharedComboboxField
                                        label="Agence"
                                        error={form.errors.agence_id}
                                        value={form.data.agence_id}
                                        placeholder="Sélectionner"
                                        options={agencyOptions.map((item) => ({
                                            value: String(item.id),
                                            label: getAgencyLabel(item),
                                        }))}
                                        onSelect={(value) => form.setData('agence_id', value)}
                                    />
                                )}

                                <div className="grid gap-4 md:grid-cols-3">
                                    <InfoChip
                                        label="Plan"
                                        value={plan.label}
                                        icon={Ticket}
                                    />
                                    <InfoChip
                                        label="Tarif"
                                        value={formatMoney(plan.monthlyPrice)}
                                        icon={CircleDollarSign}
                                    />
                                    <InfoChip
                                        label="Cycle"
                                        value={plan.cycle}
                                        icon={CalendarDays}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Periode de souscription</CardTitle>
                                <CardDescription>
                                    Les montants sont recalculés automatiquement en fonction de la durée sélectionnée.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="mt-3 space-y-4 p-6">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <Field
                                        label="Date de debut"
                                        error={form.errors.abonnement_start}
                                    >
                                        <Input
                                            type="date"
                                            value={form.data.abonnement_start}
                                            onChange={(event) =>
                                                form.setData(
                                                    'abonnement_start',
                                                    event.target.value
                                                )
                                            }
                                        />
                                    </Field>

                                    

                                    <Field
                                        label="Durée (en mois)"
                                        error={form.errors.duree_mois}
                                    >
                                        <Select
                                            value={String(form.data.duree_mois ?? '')}
                                            onValueChange={(value) =>
                                                form.setData('duree_mois', Number(value))
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Sélectionner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {durationOptions.map((duration) => (
                                                    <SelectItem
                                                        key={duration.key}
                                                        value={String(duration.months)}
                                                    >
                                                        {duration.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field
                                        label="Date de fin"
                                        error={form.errors.abonnement_end}
                                    >
                                        <Input
                                            type="date"
                                            value={form.data.abonnement_end}
                                            readOnly
                                            tabIndex={-1}
                                            className="bg-slate-100 text-slate-500"
                                        />
                                    </Field>

                                    <Field
                                        label="Notes internes"
                                        error={form.errors.abonnement_notes}
                                        className="md:col-span-2"
                                    >
                                        <Textarea
                                            value={form.data.abonnement_notes}
                                            onChange={(event) =>
                                                form.setData(
                                                    'abonnement_notes',
                                                    event.target.value
                                                )
                                            }
                                            placeholder="Ajouter une note pour le suivi du dossier"
                                            className={textareaClassName}
                                        />
                                    </Field>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Modules fixes supplementaires</CardTitle>
                         
                            </CardHeader>
                            <CardContent className="mt-3 p-6">
                                {moduleOptions.length ? (
                                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        {moduleOptions.map((module) => {
                                            const checked = (form.data.options ?? []).some(
                                                (option) =>
                                                    String(option) === String(module.id)
                                            );

                                            const displayedPrice =
                                                module.totalPrice > 0
                                                    ? module.totalPrice
                                                    : module.monthlyPrice *
                                                      Number(form.data.duree_mois ?? 0);

                                            return (
                                                <div
                                                    key={module.key}
                                                    className="rounded-2xl border border-[#e2e8f0] bg-white p-4 shadow-sm"
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div className="space-y-1">
                                                            <p className="text-base font-semibold text-[#0f172a]">
                                                                {module.label}
                                                            </p>
                                                            {module.description ? (
                                                                <p className="text-sm leading-6 text-[#5f7182]">
                                                                    {module.description}
                                                                </p>
                                                            ) : null}
                                                        </div>
                                                        <Switch
                                                            checked={checked}
                                                            onCheckedChange={(nextChecked) => {
                                                                const nextOptions = nextChecked
                                                                    ? [
                                                                          ...(form.data.options ?? []),
                                                                          module.id,
                                                                      ]
                                                                    : (form.data.options ?? []).filter(
                                                                          (option) =>
                                                                              String(option) !==
                                                                              String(module.id)
                                                                      );

                                                                form.setData(
                                                                    'options',
                                                                    nextOptions
                                                                );
                                                            }}
                                                        />
                                                    </div>

                                                    <div className="mt-4 flex items-center justify-between gap-3">
                                                      
                                                        <p className="text-sm font-semibold text-slate-900">
                                                            {formatMoney(displayedPrice)}
                                                        </p>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <EmptyState
                                        title="Aucun module disponible"
                                        description="Les modules fixes de souscription ne sont pas encore exposes par la configuration."
                                    />
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                <Card className="sticky top-6 rounded-2xl border-[#c8d4de] shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Recapitulatif</CardTitle>
                                <CardDescription>
                                    Contrôle les données avant validation.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-5 p-6">
                                <div className="mt-3 space-y-3 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                    <SummaryLine label="Agence" value={getAgencyLabel(selectedAgency)} />
                                    <SummaryLine label="Plan" value={plan.label} />
                                    <SummaryLine
                                        label="Debut"
                                        value={formatDate(form.data.abonnement_start)}
                                    />
                                    <SummaryLine
                                        label="Fin"
                                        value={formatDate(form.data.abonnement_end)}
                                    />
                                    <SummaryLine
                                        label="Durée"
                                        value={`${form.data.duree_mois ?? 0} mois`}
                                    />
                                </div>

                                <div className="grid gap-3">
                                    <SummaryMetric
                                        label="Base"
                                        value={formatMoney(baseTotal)}
                                        tone="text-[#00559b]"
                                    />
                                    <SummaryMetric
                                        label="Modules"
                                        value={formatMoney(modulesTotal)}
                                        tone="text-emerald-600"
                                    />
                                    <SummaryMetric
                                        label="Total"
                                        value={formatMoney(total)}
                                        tone="text-slate-900"
                                    />
                                </div>

                              

                             

                                <div className="flex flex-col gap-3 pt-1">
                                    <Button
                                        type="submit"
                                        className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]"
                                        disabled={form.processing}
                                    >

                                        {actionLabel}

                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>

                                {Object.values(form.errors).filter(Boolean).length ? (
                                    <div className="space-y-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                                        {Object.values(form.errors).map((error) => (
                                            <p key={error}>{error}</p>
                                        ))}
                                    </div>
                                ) : null}
                            </CardContent>
                        </Card>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

function Field({ label, error, className = '', children }) {
    return (
        <div className={`space-y-2 ${className}`}>
            <Label className="text-sm font-medium text-[#0f172a]">{label}</Label>
            {children}
            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function ComboboxField({
    label,
    required = false,
    error,
    value,
    placeholder = 'Sélectionner',
    options = [],
    onSelect,
    disabled = false,
}) {
    const rootRef = useRef(null);
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');

    const selectedLabel =
        options.find((option) => String(option.value) === String(value))?.label ?? '';

    const filteredOptions = useMemo(() => {
        const term = search.trim().toLowerCase();
        if (!term) return options;

        return options.filter((option) =>
            String(option.label ?? '')
                .toLowerCase()
                .includes(term)
        );
    }, [options, search]);

    useEffect(() => {
        if (!open) {
            setSearch(selectedLabel);
        }
    }, [open, selectedLabel]);

    useEffect(() => {
        function handlePointerDown(event) {
            if (rootRef.current && !rootRef.current.contains(event.target)) {
                setOpen(false);
            }
        }

        document.addEventListener('mousedown', handlePointerDown);
        document.addEventListener('touchstart', handlePointerDown);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
            document.removeEventListener('touchstart', handlePointerDown);
        };
    }, []);

    return (
        <div ref={rootRef} className="space-y-2">
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>

            <div className="relative w-full">
                <div
                    onClick={() => {
                        if (!disabled) setOpen((current) => !current);
                    }}
                    onKeyDown={(event) => {
                        if (disabled) return;
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            setOpen((current) => !current);
                        }
                    }}
                    className={[
                        'flex h-11 w-full items-center justify-between rounded-xl border bg-white px-3 text-left transition',
                        disabled
                            ? 'cursor-not-allowed border-[#e2e8f0] bg-slate-50 text-slate-400'
                            : open
                                ? 'border-[#00559b]'
                                : 'border-[#c8d4de]',
                    ].join(' ')}
                    role="button"
                    tabIndex={disabled ? -1 : 0}
                >
                    <div className="flex min-w-0 items-center gap-2">
                        <Search className={disabled ? 'h-4 w-4 shrink-0 text-slate-300' : 'h-4 w-4 shrink-0 text-[#94a3b8]'} />
                        <span className={[
                            'truncate text-sm',
                            selectedLabel ? 'text-[#0f172a]' : 'text-[#8798a5]',
                            disabled ? 'text-slate-400' : '',
                        ].join(' ')}>
                            {selectedLabel || placeholder}
                        </span>
                    </div>

                    <div className="ml-3 flex items-center gap-2">
                        {!disabled && value ? (
                            <button
                                type="button"
                                className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[#5f7182] transition hover:bg-[#f1f5f9] hover:text-[#0f172a]"
                                onClick={(event) => {
                                    event.stopPropagation();
                                    onSelect('');
                                    setSearch('');
                                    setOpen(false);
                                }}
                                aria-label={`Effacer ${label}`}
                            >
                                <X className="h-4 w-4" />
                            </button>
                        ) : null}
                        <ChevronDown className="h-4 w-4 shrink-0 text-[#94a3b8]" />
                    </div>
                </div>

                {open && !disabled ? (
                    <div className="absolute left-0 right-0 top-full z-30 mt-2 w-full overflow-hidden rounded-xl border border-[#c8d4de] bg-white shadow-lg">
                        <div className="border-b border-[#e2e8f0] p-2">
                            <div className="flex h-10 items-center rounded-lg border border-[#c8d4de] bg-white px-3 focus-within:border-[#00559b]">
                                <Search className="h-4 w-4 shrink-0 text-[#94a3b8]" />
                                <input
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder={`Rechercher ${label.toLowerCase()}...`}
                                    className="h-9 w-full border-0 bg-transparent px-2 text-sm text-[#0f172a] outline-none placeholder:text-[#8798a5]"
                                    autoFocus
                                />
                            </div>
                        </div>

                        <div className="max-h-60 overflow-y-auto p-1">
                            {filteredOptions.length ? (
                                filteredOptions.map((option) => {
                                    const active = String(option.value) === String(value);

                                    return (
                                        <button
                                            key={option.value}
                                            type="button"
                                            className={[
                                                'flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition',
                                                active
                                                    ? 'bg-[#eaf4fb] text-[#00559b]'
                                                    : 'text-[#0f172a] hover:bg-[#f8fafc]',
                                            ].join(' ')}
                                            onClick={() => {
                                                onSelect(option.value);
                                                setSearch(option.label);
                                                setOpen(false);
                                            }}
                                        >
                                            <span className="truncate">{option.label}</span>
                                            {active ? <Check className="h-4 w-4" /> : null}
                                        </button>
                                    );
                                })
                            ) : (
                                <div className="px-3 py-2 text-sm text-[#5f7182]">
                                    Aucun résultat
                                </div>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>

            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function InfoChip({ label, value, icon: Icon }) {
    return (
        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2">
            <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-[#94a3b8]">
                <Icon className="h-3.5 w-3.5" />
                {label}
            </div>
            <p className="mt-1 text-sm font-semibold text-[#0f172a]">{value}</p>
        </div>
    );
}

function SummaryLine({ label, value }) {
    return (
        <div className="flex items-start justify-between gap-4 text-sm">
            <span className="text-[#5f7182]">{label}</span>
            <span className="text-right font-semibold text-[#0f172a]">{value}</span>
        </div>
    );
}

function SummaryMetric({ label, value, tone }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-white p-4">
            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">
                {label}
            </p>
            <p className={`mt-2 text-lg font-semibold ${tone}`}>{value}</p>
        </div>
    );
}

function EmptyState({ title, description }) {
    return (
        <div className="rounded-3xl border border-dashed border-[#e2e8f0] bg-[#f8fafc] p-6 text-center">
            <p className="text-sm font-semibold text-[#0f172a]">{title}</p>
            <p className="mt-2 text-sm leading-6 text-[#5f7182]">{description}</p>
        </div>
    );
}

