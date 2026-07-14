import { useEffect, useMemo, useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';

const toDateValue = (value) => {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return date.toISOString().slice(0, 10);
};

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

const selectClassName =
    'flex h-10 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[110px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

export default function Form({ mode, agence = {}, regions = [], responsables = [], tarifications = {}, villes = [] }) {
    const isEdit = mode === 'edit';
    const [responsableMode, setResponsableMode] = useState('existing');

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

    useEffect(() => {
        setSelectedRegion(form.data.region);
    }, [form.data.region]);

    const filteredVilles = useMemo(() => {
        return (villes ?? []).filter((ville) => String(ville.region_id ?? ville.region?.id ?? '') === String(selectedRegion ?? ''));
    }, [villes, selectedRegion]);

    const selectedModules = useMemo(() => {
        return moduleOptions.filter((module) => form.data.options.includes(Number(module.id)));
    }, [moduleOptions, form.data.options]);

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
                                <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                            </Field>
                                <Field label="Adresse" error={form.errors.adresse}>
                                    <textarea
                                        value={form.data.adresse}
                                        onChange={(e) => form.setData('adresse', e.target.value)}
                                        className={textareaClassName}
                                    />
                                </Field>
                            <div className="grid gap-4 md:grid-cols-2">
                                <Field label="Téléphone 1" error={form.errors.tel1}>
                                    <Input value={form.data.tel1} onChange={(e) => form.setData('tel1', e.target.value)} />
                                </Field>
                                <Field label="Téléphone 2" error={form.errors.tel2}>
                                    <Input value={form.data.tel2} onChange={(e) => form.setData('tel2', e.target.value)} />
                                </Field>
                                <Field label="Email principal" error={form.errors.email1}>
                                    <Input type="email" value={form.data.email1} onChange={(e) => form.setData('email1', e.target.value)} />
                                </Field>
                                <Field label="Email secondaire" error={form.errors.email2}>
                                    <Input type="email" value={form.data.email2} onChange={(e) => form.setData('email2', e.target.value)} />
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
                                <Field label="Région" error={form.errors.region} className="mt-4">
                                    <select
                                        value={form.data.region}
                                        onChange={(e) => {
                                            form.setData('region', e.target.value);
                                            setSelectedRegion(e.target.value);
                                        }}
                                        className={selectClassName}
                                    >
                                        <option value="">Sélectionner</option>
                                        {regions.map((region) => (
                                            <option key={region.id ?? region.region_id} value={region.id ?? region.region_id}>
                                                {region.name}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <Field label="Ville" error={form.errors.ville_id}  className="mt-4">
                                    <select
                                        value={form.data.ville_id}
                                        onChange={(e) => form.setData('ville_id', e.target.value)}
                                        className={selectClassName}
                                    >
                                        <option value="">Sélectionner</option>
                                        {filteredVilles.map((ville) => (
                                            <option key={ville.id ?? ville.ville_id} value={ville.id ?? ville.ville_id}>
                                                {ville.name}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            </div>
                        </Section>

                        <Section title="Responsable" step="03">
                            <div className="space-y-4">
                                <div className="flex flex-wrap gap-3 mt-4">
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
                                            className={`inline-flex h-10 items-center justify-center rounded-full border px-4 text-sm font-medium ${
                                                responsableMode === value
                                                    ? 'border-slate-900 bg-slate-900 text-white'
                                                    : 'border-slate-200 bg-white text-slate-900'
                                            }`}
                                        >
                                            {label}
                                        </button>
                                    ))}
                                </div>

                                {responsableMode === 'existing' ? (
                                    <Field label="Responsable existant" error={form.errors.responsable_id} className="mt-4">
                                        <select
                                            value={form.data.responsable_id}
                                            onChange={(e) => form.setData('responsable_id', e.target.value)}
                                            className={selectClassName}
                                        >
                                            <option value="">Sélectionner</option>
                                            {responsables.map((user) => (
                                                <option key={user.id_users ?? user.id} value={user.id_users ?? user.id}>
                                                    {user.name} - {user.email}
                                                </option>
                                            ))}
                                        </select>
                                    </Field>
                                ) : (
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Nom complet" error={form.errors.new_responsable_name}>
                                            <Input value={form.data.new_responsable_name} onChange={(e) => form.setData('new_responsable_name', e.target.value)} />
                                        </Field>
                                        <Field label="Email" error={form.errors.new_responsable_email}>
                                            <Input type="email" value={form.data.new_responsable_email} onChange={(e) => form.setData('new_responsable_email', e.target.value)} />
                                        </Field>
                                        <Field label="Mot de passe" error={form.errors.new_responsable_password}>
                                            <Input type="password" value={form.data.new_responsable_password} onChange={(e) => form.setData('new_responsable_password', e.target.value)} />
                                        </Field>
                                        <Field label="Confirmation" error={form.errors.new_responsable_password_confirmation}>
                                            <Input
                                                type="password"
                                                value={form.data.new_responsable_password_confirmation}
                                                onChange={(e) => form.setData('new_responsable_password_confirmation', e.target.value)}
                                            />
                                        </Field>
                                        <Field label="Téléphone 1" error={form.errors.new_responsable_tel1}>
                                            <Input value={form.data.new_responsable_tel1} onChange={(e) => form.setData('new_responsable_tel1', e.target.value)} />
                                        </Field>
                                        <Field label="Téléphone 2" error={form.errors.new_responsable_tel2}>
                                            <Input value={form.data.new_responsable_tel2} onChange={(e) => form.setData('new_responsable_tel2', e.target.value)} />
                                        </Field>
                                        <Field label="Adresse" error={form.errors.new_responsable_adresse} className="md:col-span-2">
                                            <textarea
                                                value={form.data.new_responsable_adresse}
                                                onChange={(e) => form.setData('new_responsable_adresse', e.target.value)}
                                                className={textareaClassName}
                                            />
                                        </Field>
                                    </div>
                                )}
                            </div>
                        </Section>

                        <Section title="Abonnement" step="04">
                            <div className="grid gap-4 md:grid-cols-2 mt-4">
                                <Field label="Date début" error={form.errors.abonnement_start}>
                                    <Input type="date" value={form.data.abonnement_start} onChange={(e) => form.setData('abonnement_start', e.target.value)} />
                                </Field>
                                <Field label="Date fin" error={form.errors.abonnement_end}>
                                    <Input type="date" value={form.data.abonnement_end} onChange={(e) => form.setData('abonnement_end', e.target.value)} />
                                </Field>
                                <Field label="Durée (mois)" error={form.errors.duree_mois}>
                                    <select
                                        value={form.data.duree_mois}
                                        onChange={(e) => form.setData('duree_mois', e.target.value)}
                                        className={selectClassName}
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
                                    />
                                </Field>
                            </div>

                            <div className=" rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p className="text-sm font-medium text-slate-900">Modules additionnels</p>
                                <div className="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                    {moduleOptions.map((module) => {
                                        const id = Number(module.id);
                                        const checked = form.data.options.includes(id);
                                        return (
                                            <label key={id} className="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                                <input
                                                    type="checkbox"
                                                    checked={checked}
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
                                    <p className="mt-2 text-lg font-semibold text-slate-900">{formatMoney(computedBaseTotal)}</p>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p className="text-sm text-slate-500">Modules</p>
                                    <p className="mt-2 text-lg font-semibold text-slate-900">{formatMoney(computedModulesTotal)}</p>
                                </div>
                                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p className="text-sm text-slate-500">Total</p>
                                    <p className="mt-2 text-lg font-semibold text-[#00559b]">{formatMoney(computedTotal)}</p>
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
                                <Summary label="Mode responsable" value={responsableMode} />
                                <Summary label="Statut" value={form.data.statut} />
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
