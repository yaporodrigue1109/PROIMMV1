import { useEffect, useMemo, useRef, useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import { Check, ChevronDown, Search, X } from 'lucide-react';
import flags from 'react-phone-number-input/flags';
import PhoneInputBase from 'react-phone-number-input';
import 'react-phone-number-input/style.css';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';
import { ComboboxField as SharedComboboxField } from '../../../components/ui/combobox-field';

const toDateValue = (value) => {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return date.toISOString().slice(0, 10);
};

const addMonthsToDate = (dateValue, months) => {
    if (!dateValue || !months) return '';

    const date = new Date(`${dateValue}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '';

    const next = new Date(date);
    next.setMonth(next.getMonth() + Number(months));

    return next.toISOString().slice(0, 10);
};

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

const selectClassName =
    'flex h-10 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[110px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

function CountrySelect({ value, onChange, options }) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const containerRef = useRef(null);

    useEffect(() => {
        const onDocumentClick = (event) => {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', onDocumentClick);
        return () => document.removeEventListener('mousedown', onDocumentClick);
    }, []);

    const filtered = options.filter((option) =>
        `${option.label} ${option.value}`.toLowerCase().includes(query.toLowerCase())
    );

    const Flag = value ? flags[value] : null;

    return (
        <div className="relative shrink-0" ref={containerRef}>
            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                className="flex h-full items-center gap-1.5 rounded-l-md border-r border-[#c8d4de] bg-white px-2.5 text-sm text-[#5f7182] transition-colors hover:bg-[#f8fafc]"
            >
                {Flag ? (
                    <Flag title={value} className="h-4 w-5 rounded-sm object-cover" />
                ) : (
                    <span className="h-4 w-5" />
                )}
                <span className="text-xs font-medium">+</span>
            </button>

            {open ? (
                <div className="absolute left-0 top-[calc(100%+4px)] z-50 w-64 overflow-hidden rounded-md border border-[#c8d4de] bg-white shadow-lg">
                    <div className="flex items-center gap-2 border-b border-[#e2e8f0] px-2.5 py-2">
                        <input
                            autoFocus
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Rechercher un pays..."
                            className="w-full border-0 bg-transparent text-sm text-[#0f172a] outline-none placeholder:text-[#94a3b8]"
                        />
                    </div>
                    <ul className="max-h-60 overflow-y-auto py-1">
                        {filtered.length === 0 ? (
                            <li className="px-3 py-2 text-sm text-[#94a3b8]">Aucun résultat</li>
                        ) : (
                            filtered.map((option) => {
                                const CFlag = flags[option.value];

                                return (
                                    <li key={option.value}>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                onChange(option.value);
                                                setOpen(false);
                                                setQuery('');
                                            }}
                                            className="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-[#eaf4fb]"
                                        >
                                            {CFlag ? (
                                                <CFlag className="h-4 w-5 shrink-0 rounded-sm object-cover" />
                                            ) : (
                                                <span className="h-4 w-5 shrink-0" />
                                            )}
                                            <span className="flex-1 truncate text-[#0f172a]">
                                                {option.label}
                                            </span>
                                        </button>
                                    </li>
                                );
                            })
                        )}
                    </ul>
                </div>
            ) : null}
        </div>
    );
}

const PhoneInput = ({ label, error, value, onChange, placeholder }) => {
    return (
        <Field label={label} error={error}>
            <PhoneInputBase
                international
                defaultCountry="CI"
                countrySelectComponent={CountrySelect}
                value={value}
                onChange={(val) => onChange({ target: { value: val ?? '' } })}
                placeholder={placeholder}
                className="phone-input-custom flex h-10 items-stretch rounded-md border border-[#c8d4de] bg-white shadow-sm transition-colors focus-within:border-[#00559b] focus-within:ring-2 focus-within:ring-[#00559b]/20"
            />
        </Field>
    );
};

const ComboboxField = ({ label, error, value, placeholder, options, onSelect, disabled = false }) => {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');
    const rootRef = useRef(null);

    const selectedLabel = options.find((option) => String(option.value) === String(value))?.label ?? '';

    useEffect(() => {
        const onDocumentClick = (event) => {
            if (rootRef.current && !rootRef.current.contains(event.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', onDocumentClick);
        return () => document.removeEventListener('mousedown', onDocumentClick);
    }, []);

    const filteredOptions = useMemo(() => {
        if (!search.trim()) {
            return options;
        }

        const term = search.toLowerCase();
        return options.filter((option) =>
            `${option.label} ${option.value}`.toLowerCase().includes(term)
        );
    }, [options, search]);

    return (
        <Field label={label} error={error}>
            <div ref={rootRef} className="relative">
                <div
                    role="button"
                    tabIndex={disabled ? -1 : 0}
                    aria-disabled={disabled}
                    onClick={() => {
                        if (!disabled) {
                            setOpen((current) => !current);
                        }
                    }}
                    onKeyDown={(event) => {
                        if (disabled) return;
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            setOpen((current) => !current);
                        }
                    }}
                    className={`flex h-10 w-full items-center justify-between rounded-md border bg-white px-3 text-left text-sm shadow-sm transition ${
                        disabled
                            ? 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400'
                            : open
                                ? 'border-[#00559b] ring-2 ring-[#00559b]/20'
                                : 'border-[#c8d4de]'
                    }`}
                >
                    <div className="flex min-w-0 items-center gap-2">
                        <Search className="h-4 w-4 shrink-0 text-slate-400" />
                        <span className={selectedLabel ? 'truncate text-[#0f172a]' : 'truncate text-slate-400'}>
                            {selectedLabel || placeholder}
                        </span>
                    </div>
                    <div className="ml-3 flex items-center gap-2">
                        {value ? (
                            <button
                                type="button"
                                className="inline-flex h-6 w-6 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                onClick={(event) => {
                                    event.stopPropagation();
                                    onSelect('');
                                    setSearch('');
                                    setOpen(false);
                                }}
                                aria-label={`Effacer ${label}`}
                            >
                                <X className="h-3.5 w-3.5" />
                            </button>
                        ) : null}
                        <ChevronDown className="h-4 w-4 shrink-0 text-slate-400" />
                    </div>
                </div>

                {open && !disabled ? (
                    <div className="absolute left-0 right-0 top-[calc(100%+6px)] z-40 overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg">
                        <div className="border-b border-slate-100 p-2">
                            <div className="flex h-10 items-center rounded-md border border-slate-200 bg-white px-3 focus-within:border-[#00559b]">
                                <Search className="h-4 w-4 shrink-0 text-slate-400" />
                                <input
                                    autoFocus
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder={`Rechercher ${label.toLowerCase()}...`}
                                    className="h-9 w-full border-0 bg-transparent px-2 text-sm text-[#0f172a] outline-none placeholder:text-slate-400"
                                />
                            </div>
                        </div>
                        <div className="max-h-56 overflow-y-auto p-1">
                            {filteredOptions.length ? (
                                filteredOptions.map((option) => {
                                    const active = String(option.value) === String(value);

                                    return (
                                        <button
                                            key={option.value}
                                            type="button"
                                            onClick={() => {
                                                onSelect(option.value);
                                                setSearch(option.label);
                                                setOpen(false);
                                            }}
                                            className={`flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm ${
                                                active
                                                    ? 'bg-[#eaf4fb] text-[#00559b]'
                                                    : 'text-[#0f172a] hover:bg-slate-50'
                                            }`}
                                        >
                                            <span className="truncate">{option.label}</span>
                                            {active ? <Check className="h-4 w-4" /> : null}
                                        </button>
                                    );
                                })
                            ) : (
                                <div className="px-3 py-2 text-sm text-slate-500">Aucun résultat</div>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
        </Field>
    );
};

export default function Form({ mode, agence = {}, regions = [], responsables = [], tarifications = {}, villes = [] }) {
    const isEdit = mode === 'edit';
    const [responsableMode, setResponsableMode] = useState('existing');
    const [regionOpen, setRegionOpen] = useState(false);
    const [regionSearch, setRegionSearch] = useState('');
    const [villeOpen, setVilleOpen] = useState(false);
    const [villeSearch, setVilleSearch] = useState('');

    const basePrice = Number(tarifications?.plan_prix_mensuel ?? 0);
    const durationOptions = tarifications?.durees ?? [1, 3, 6, 12, 24, 36];
    const moduleOptions = tarifications?.modules ?? [];

    const [selectedRegion, setSelectedRegion] = useState(agence?.region_id ?? '');

    const form = useForm({
        name: agence?.name ?? '',
        adresse: agence?.adresse ?? '',
        tel1: agence?.tel1 ?? '',
        tel2: agence?.tel2 ?? '',
        email1: agence?.email1 ?? '',
        email2: agence?.email2 ?? '',
        statut: agence?.statut ?? 'en_demo',
        region: agence?.region_id ?? '',
        ville_id: agence?.ville_id ?? '',
        responsable_mode: 'existing',
        responsable_id: agence?.responsable_id ?? '',
        new_responsable_name: '',
        new_responsable_email: '',
        new_responsable_password: '',
        new_responsable_password_confirmation: '',
        new_responsable_tel1: '',
        new_responsable_tel2: '',
        new_responsable_adresse: '',
        abonnement_start: toDateValue(agence?.abonnement_start),
        abonnement_end: toDateValue(agence?.abonnement_end),
        duree_mois: agence?.duree_mois ?? 12,
        prix_base_mensuel: basePrice,
        montant_base_total: Number(agence?.montant_base_total ?? 0),
        montant_total: Number(agence?.montant_total ?? 0),
        options: [],
    });

    const subscriptionEnabled = form.data.statut === 'active';

    useEffect(() => {
        setSelectedRegion(form.data.region);
    }, [form.data.region]);

    const filteredVilles = useMemo(() => {
        return (villes ?? []).filter((ville) => String(ville.region_id ?? ville.region?.id ?? '') === String(selectedRegion ?? ''));
    }, [villes, selectedRegion]);

    const regionOptions = useMemo(
        () =>
            (regions ?? []).map((region) => ({
                value: String(region.id ?? region.region_id),
                label: region.name,
            })),
        [regions]
    );

    const villeOptions = useMemo(
        () =>
            filteredVilles.map((ville) => ({
                value: String(ville.id ?? ville.ville_id),
                label: ville.name,
            })),
        [filteredVilles]
    );

    const selectedModules = useMemo(() => {
        if (!subscriptionEnabled) {
            return [];
        }

        return moduleOptions.filter((module) => form.data.options.includes(Number(module.id)));
    }, [moduleOptions, form.data.options, subscriptionEnabled]);

    const computedBaseTotal = useMemo(() => Number(form.data.prix_base_mensuel || 0) * Number(form.data.duree_mois || 0), [form.data.prix_base_mensuel, form.data.duree_mois]);
    const computedModulesTotal = useMemo(
        () => selectedModules.reduce((sum, module) => sum + Number(module.prix_mensuel || 0) * Number(form.data.duree_mois || 0), 0),
        [selectedModules, form.data.duree_mois]
    );
    const computedTotal = computedBaseTotal + computedModulesTotal;

    useEffect(() => {
        form.setData('montant_base_total', computedBaseTotal);
        form.setData('montant_total', computedTotal);
    }, [computedBaseTotal, computedTotal]);

    useEffect(() => {
        if (Number(form.data.prix_base_mensuel) !== basePrice) {
            form.setData('prix_base_mensuel', basePrice);
        }
    }, [basePrice, form.data.prix_base_mensuel]);

    useEffect(() => {
        if (form.data.statut === 'active' && !form.data.abonnement_start) {
            form.setData('abonnement_start', toDateValue(new Date()));
        }
    }, [form.data.statut, form.data.abonnement_start]);

    useEffect(() => {
        if (!subscriptionEnabled && form.data.options.length) {
            form.setData('options', []);
        }
    }, [form.data.options.length, subscriptionEnabled]);

    useEffect(() => {
        const nextEnd = addMonthsToDate(form.data.abonnement_start, form.data.duree_mois);

        if (nextEnd && form.data.abonnement_end !== nextEnd) {
            form.setData('abonnement_end', nextEnd);
        }
    }, [form.data.abonnement_start, form.data.duree_mois]);

    const submit = (event) => {
        event.preventDefault();
        const url = isEdit ? `/admin/agences/update/${agence.agence_id}` : '/admin/agences/store';
        const method = isEdit ? 'put' : 'post';

        form[method](url, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title={isEdit ? 'Modifier une agence' : 'Ajouter une agence'}>
            <div className="space-y-6">
                <Card className="border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                           
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900 mt-4">
                                {isEdit ? 'Modifier une agence' : 'Ajouter une agence'}
                            </h1>
                        
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild variant="outline" className=" mt-4 h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/agences">Retour</Link>
                            </Button>
                            {isEdit ? (
                                <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                    <Link href={`/admin/agences/${agence.code_agence}`}>Voir la fiche</Link>
                                </Button>
                            ) : null}
                        </div>
                    </CardContent>
                </Card>

                <form onSubmit={submit} className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div className="space-y-6">
                        <Section title="Identité" step="01" >
                            <Field label="Nom de l'agence" error={form.errors.name} className="mt-4">
                                <Input
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    placeholder="Ex: Agence Centrale Abidjan"
                                />
                            </Field>
                                <Field label="Adresse" error={form.errors.adresse}>
                                    <textarea
                                        value={form.data.adresse}
                                        onChange={(e) => form.setData('adresse', e.target.value)}
                                        placeholder="Ex: Cocody, Boulevard Latrille"
                                        className={textareaClassName}
                                    />
                            </Field>
                            <div className="grid gap-4 md:grid-cols-2">
                                <PhoneInput
                                    label="Téléphone 1"
                                    error={form.errors.tel1}
                                    value={form.data.tel1}
                                    onChange={(e) => form.setData('tel1', e.target.value)}
                                    placeholder="+225 07 00 00 00 00"
                                />
                                <PhoneInput
                                    label="Téléphone 2"
                                    error={form.errors.tel2}
                                    value={form.data.tel2}
                                    onChange={(e) => form.setData('tel2', e.target.value)}
                                    placeholder="+225 05 00 00 00 00"
                                />
                                <Field label="Email principal" error={form.errors.email1}>
                                    <Input
                                        type="email"
                                        value={form.data.email1}
                                        onChange={(e) => form.setData('email1', e.target.value)}
                                        placeholder="Ex: contact@agence.com"
                                    />
                                </Field>
                                <Field label="Email secondaire" error={form.errors.email2}>
                                    <Input
                                        type="email"
                                        value={form.data.email2}
                                        onChange={(e) => form.setData('email2', e.target.value)}
                                        placeholder="Ex: support@agence.com"
                                    />
                                </Field>
                                <Field label="Statut" error={form.errors.statut}>
                                    <select
                                        value={form.data.statut}
                                        onChange={(e) => form.setData('statut', e.target.value)}
                                        className={selectClassName}
                                    >
                                        <option value="en_demo">En démo</option>
                                        <option value="active">Active</option>
                                    </select>
                                </Field>
                            </div>
                        </Section>

                        <Section title="Localisation" step="02">
                            <div className="grid gap-4 md:grid-cols-2">
                                <ComboboxField
                                    label="Région"
                                    error={form.errors.region}
                                    value={form.data.region}
                                    placeholder="Sélectionner une région"
                                    options={regionOptions}
                                    open={regionOpen}
                                    onOpenChange={setRegionOpen}
                                    searchValue={regionSearch}
                                    onSearchChange={setRegionSearch}
                                    onSelect={(value) => {
                                        form.setData('region', value);
                                        setSelectedRegion(value);
                                        form.setData('ville_id', '');
                                        setVilleSearch('');
                                        setVilleOpen(false);
                                    }}
                                    emptyLabel="Aucune région trouvée"
                                />
                                <ComboboxField
                                    label="Ville"
                                    error={form.errors.ville_id}
                                    value={form.data.ville_id}
                                    placeholder={form.data.region ? 'Sélectionner une ville' : 'Choisissez une région déabord'}
                                    options={villeOptions}
                                    open={villeOpen}
                                    onOpenChange={setVilleOpen}
                                    searchValue={villeSearch}
                                    onSearchChange={setVilleSearch}
                                    onSelect={(value) => form.setData('ville_id', value)}
                                    emptyLabel={form.data.region ? 'Aucune ville trouvée' : 'Sélectionnez déabord une région'}
                                    disabled={!form.data.region}
                                />
                            </div>
                        </Section>

                        <Section title="Responsable" step="03">
                            <div className="space-y-4">
                                <div className="mt-4 grid grid-cols-2 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                    {[
                                        ['existing', 'Sélectionner un existant'],
                                        ['new', 'Créer un nouveau'],
                                    ].map(([value, label]) => (
                                        <button
                                            key={value}
                                            type="button"
                                            onClick={() => {
                                                setResponsableMode(value);
                                                form.setData('responsable_mode', value);
                                            }}
                                            className={`inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-medium transition ${
                                                responsableMode === value
                                                    ? 'bg-white text-[#00559b] shadow-sm ring-1 ring-inset ring-[#00559b]/15'
                                                    : 'text-slate-500 hover:text-slate-900'
                                            }`}
                                        >
                                            {label}
                                        </button>
                                    ))}
                                </div>

                                {responsableMode === 'existing' ? (
                                    <SharedComboboxField
                                        label="Responsable existant"
                                        error={form.errors.responsable_id}
                                        value={form.data.responsable_id}
                                        placeholder="Sélectionner un responsable"
                                        options={responsables.map((user) => ({
                                            value: user.id_users ?? user.id,
                                            label: `${user.name}${user.email ? ` - ${user.email}` : ''}`,
                                        }))}
                                        onSelect={(value) => form.setData('responsable_id', value)}
                                    />
                                ) : (
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Nom complet" error={form.errors.new_responsable_name}>
                                            <Input
                                                value={form.data.new_responsable_name}
                                                onChange={(e) => form.setData('new_responsable_name', e.target.value)}
                                                placeholder="Ex: Jean Dupont"
                                            />
                                        </Field>
                                        <Field label="Email" error={form.errors.new_responsable_email}>
                                            <Input
                                                type="email"
                                                value={form.data.new_responsable_email}
                                                onChange={(e) => form.setData('new_responsable_email', e.target.value)}
                                                placeholder="Ex: jean@agence.com"
                                            />
                                        </Field>
                                        <Field label="Mot de passe" error={form.errors.new_responsable_password}>
                                            <Input
                                                type="password"
                                                value={form.data.new_responsable_password}
                                                onChange={(e) => form.setData('new_responsable_password', e.target.value)}
                                                placeholder="Au moins 8 caractères"
                                            />
                                        </Field>
                                        <Field label="Confirmation" error={form.errors.new_responsable_password_confirmation}>
                                            <Input
                                                type="password"
                                                value={form.data.new_responsable_password_confirmation}
                                                onChange={(e) => form.setData('new_responsable_password_confirmation', e.target.value)}
                                                placeholder="Répéter le mot de passe"
                                            />
                                        </Field>
                                        <PhoneInput
                                            label="Téléphone 1"
                                            error={form.errors.new_responsable_tel1}
                                            value={form.data.new_responsable_tel1}
                                            onChange={(e) => form.setData('new_responsable_tel1', e.target.value)}
                                            placeholder="+225 07 00 00 00 00"
                                        />
                                        <PhoneInput
                                            label="Téléphone 2"
                                            error={form.errors.new_responsable_tel2}
                                            value={form.data.new_responsable_tel2}
                                            onChange={(e) => form.setData('new_responsable_tel2', e.target.value)}
                                            placeholder="+225 05 00 00 00 00"
                                        />
                                        <Field label="Adresse" error={form.errors.new_responsable_adresse} className="md:col-span-2">
                                            <textarea
                                                value={form.data.new_responsable_adresse}
                                                onChange={(e) => form.setData('new_responsable_adresse', e.target.value)}
                                                placeholder="Adresse du responsable"
                                                className={textareaClassName}
                                            />
                                        </Field>
                                    </div>
                                )}
                            </div>
                        </Section>

                        <Section title="Abonnement" step="04">
                            <p className="mt-4 text-sm text-slate-500">
                                L’abonnement n’est obligatoire que si l’agence est activée. La date de fin est calculée automatiquement.
                            </p>
                            <div className="grid gap-4 md:grid-cols-2 mt-4">
                                <Field label="Date début" error={form.errors.abonnement_start}>
                                    <Input
                                        type="date"
                                        value={form.data.abonnement_start}
                                        onChange={(e) => form.setData('abonnement_start', e.target.value)}
                                        disabled={form.data.statut !== 'active'}
                                    />
                                </Field>
                                <Field label="Date fin" error={form.errors.abonnement_end}>
                                    <Input
                                        type="date"
                                        value={form.data.abonnement_end}
                                        readOnly
                                        disabled={form.data.statut !== 'active'}
                                    />
                                </Field>
                                <Field label="Durée (mois)" error={form.errors.duree_mois}>
                                    <select
                                        value={form.data.duree_mois}
                                        onChange={(e) => form.setData('duree_mois', e.target.value)}
                                        className={selectClassName}
                                        disabled={form.data.statut !== 'active'}
                                    >
                                        {durationOptions.map((duree) => (
                                            <option key={duree} value={duree}>
                                                {duree} mois
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <Field label="Prix mensuel de base" error={form.errors.prix_base_mensuel}>
                                    <Input
                                        type="number"
                                        value={form.data.prix_base_mensuel}
                                        onChange={(e) => form.setData('prix_base_mensuel', e.target.value)}
                                        placeholder="Montant de base"
                                        readOnly
                                        disabled
                                    />
                                </Field>
                            </div>

                            <div className=" rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div className="flex items-center justify-between gap-3">
                                    <p className="text-sm font-medium text-slate-900">Modules additionnels</p>
                                    {!subscriptionEnabled ? (
                                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
                                            Désactivés tant que l'agence n'est pas active
                                        </span>
                                    ) : null}
                                </div>
                                <div className={`mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 ${!subscriptionEnabled ? 'pointer-events-none opacity-50' : ''}`}>
                                    {moduleOptions.map((module) => {
                                        const id = Number(module.id);
                                        const checked = form.data.options.includes(id);
                                        return (
                                            <label key={id} className="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                                <input
                                                    type="checkbox"
                                                    checked={checked}
                                                    disabled={!subscriptionEnabled}
                                                    onChange={(e) => {
                                                        const next = e.target.checked
                                                            ? [...form.data.options, id]
                                                            : form.data.options.filter((item) => item !== id);
                                                        form.setData('options', next);
                                                    }}
                                                    className="mt-1"
                                                />
                                                <span className="min-w-0">
                                                    <span className="block text-sm font-medium text-slate-900">{module.label}</span>
                                                    <span className="block text-xs text-slate-500">{formatMoney(module.prix_mensuel ?? 0)} / mois</span>
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </div>

                            <div className=" grid gap-3 md:grid-cols-3">
                                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p className="text-sm text-slate-500">Base totale</p>
                                    <p className="mt-2 text-lg font-semibold text-slate-900">{formatMoney(subscriptionEnabled ? computedBaseTotal : 0)}</p>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p className="text-sm text-slate-500">Modules</p>
                                    <p className="mt-2 text-lg font-semibold text-slate-900">{formatMoney(subscriptionEnabled ? computedModulesTotal : 0)}</p>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p className="text-sm text-slate-500">Total</p>
                                    <p className="mt-2 text-lg font-semibold text-[#00559b]">{formatMoney(subscriptionEnabled ? computedTotal : 0)}</p>
                                </div>
                            </div>

                            <input type="hidden" value={form.data.montant_base_total} readOnly />
                            <input type="hidden" value={form.data.montant_total} readOnly />
                        </Section>
                    </div>

                    <aside className="space-y-6 xl:sticky xl:top-6 xl:self-start">
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg text-slate-900">Résumé</CardTitle>
                                <CardDescription className="text-slate-500">Avant envoi</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6 mt-4">
                                <Summary label="Mode responsable" value={responsableMode === 'existing' ? 'Responsable existant' : 'Nouveau responsable'} />
                                <Summary label="Statut" value={form.data.statut === 'active' ? 'Active' : form.data.statut === 'en_demo' ? 'En démo' : form.data.statut} />
                                <Summary label="Durée" value={`${form.data.duree_mois} mois`} />
                                <Summary label="Total" value={formatMoney(computedTotal)} />
                            </CardContent>
                        </Card>

                        <Card className="border-slate-200 shadow-sm">
                            <CardContent className="p-6">
                                <Button type="submit" disabled={form.processing} className="mt-6 h-11 w-full rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                    {isEdit ? 'Enregistrer les modifications' : "Créer l'agence"}
                                </Button>
                                {form.errors.name ? (
                                    <p className="mt-3 text-sm text-rose-600">{form.errors.name}</p>
                                ) : null}
                            </CardContent>
                        </Card>
                    </aside>
                </form>
            </div>
        </AdminLayout>
    );
}

function Section({ title, step, children }) {
    return (
        <Card className="border-slate-200 shadow-sm">
           <CardHeader className="flex flex-row items-center justify-between border-b border-slate-200 p-6">
                <div>
                    <CardTitle className="text-lg text-slate-900">{title}</CardTitle>
                    
                </div>
                <span className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                    {step}
                </span>
            </CardHeader>
            <CardContent className="space-y-6 p-6">{children}</CardContent>
        </Card>
    );
}

function Field({ label, error, children, className = '' }) {
    return (
        <label className={`block ${className}`}>
            <Label className="mb-2 block text-sm font-medium text-slate-700">{label}</Label>
            {children}
            {error ? <p className="mt-2 text-sm text-rose-600">{error}</p> : null}
        </label>
    );
}

function Summary({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-2 text-base font-semibold text-slate-900">{value}</p>
        </div>
    );
}
