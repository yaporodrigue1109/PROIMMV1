import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Building2, Eye, EyeOff, LockKeyhole, UserRound } from 'lucide-react';
import { useMemo, useState } from 'react';

import { Input } from '../../components/ui/input';
import { PhoneInput } from '../../components/ui/phone-input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import PublicLayout from './PublicLayout';

export default function Registration({ pays = [], regions = [], villes = [] }) {
    const form = useForm({
        name: '',
        adresse: '',
        tel1: '',
        email1: '',
        region: '',
        ville_id: '',
        country_code: 'CI',
        new_responsable_name: '',
        new_responsable_email: '',
        new_responsable_tel1: '',
        new_responsable_password: '',
        new_responsable_password_confirmation: '',
        accept_terms: false,
    });

    const selectedCountry = pays.find((country) => country.iso2 === form.data.country_code);
    const availableRegions = useMemo(
        () => regions.filter((region) => String(region.pays_id) === String(selectedCountry?.id)),
        [regions, selectedCountry?.id]
    );
    const availableCities = useMemo(
        () => villes.filter((ville) => String(ville.region_id) === String(form.data.region)),
        [villes, form.data.region]
    );

    const submit = (event) => {
        event.preventDefault();
        form.post('/inscription-agence', {
            preserveScroll: true,
            onFinish: () => form.reset('new_responsable_password', 'new_responsable_password_confirmation'),
        });
    };

    return (
        <PublicLayout>
            <Head title="Créer mon agence" />
            <main className="overflow-hidden bg-[#f5f8fc] px-5 py-24 text-[#111f3d] sm:px-6 lg:py-32">
                <div className="mx-auto w-full max-w-3xl">

                    <form onSubmit={submit} className="border-t border-[#0b1730]/15 pt-8">
                        <div className="flex items-start justify-between gap-5">
                            <div>
                                <h2 className="text-2xl font-medium tracking-[-0.025em] text-[#0b1730] sm:text-3xl">
                                    Créez votre espace Pros Immobilier
                                </h2>
                            </div>
                        </div>

                        <FormSection icon={Building2} title="Informations de l’agence">
                            <div className="grid gap-x-5 gap-y-6 sm:grid-cols-2">
                                <Field label="Nom de l’agence" required error={form.errors.name}><Input required value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Ex. Agence Horizon" /></Field>
                                <Field label="Téléphone" required error={form.errors.tel1}>
                                    <PhoneInput
                                        value={form.data.tel1}
                                        countries={pays.map((country) => country.iso2)}
                                        onCountryChange={(countryCode) => {
                                            if (!countryCode || countryCode === form.data.country_code) return;
                                            form.setData((data) => ({ ...data, country_code: countryCode, region: '', ville_id: '' }));
                                        }}
                                        onChange={(value) => form.setData('tel1', value)}
                                        placeholder="07 00 00 00 00"
                                        required
                                    />
                                    {selectedCountry ? <span className="block text-xs text-slate-500">{selectedCountry.name} ({selectedCountry.indicatif})</span> : null}
                                </Field>
                                <Field label="Email de l’agence" required error={form.errors.email1}><Input required type="email" value={form.data.email1} onChange={(e) => form.setData('email1', e.target.value)} placeholder="contact@agence.ci" /></Field>
                                <Field label="Adresse" required error={form.errors.adresse}><Input required value={form.data.adresse} onChange={(e) => form.setData('adresse', e.target.value)} placeholder="Cocody, Abidjan" /></Field>
                                <Field label="Région" required error={form.errors.region}>
                                    <Select required value={String(form.data.region)} onValueChange={(value) => { form.setData('region', value); form.setData('ville_id', ''); }}>
                                        <SelectTrigger aria-required="true">
                                            <SelectValue placeholder="Sélectionner une région" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableRegions.map((region) => <SelectItem key={region.id} value={String(region.id)}>{region.name}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                </Field>
                                <Field label="Ville" required error={form.errors.ville_id}>
                                    <Select required value={String(form.data.ville_id)} onValueChange={(value) => form.setData('ville_id', value)} disabled={!form.data.region}>
                                        <SelectTrigger aria-required="true" disabled={!form.data.region}>
                                            <SelectValue placeholder="Sélectionner une ville" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableCities.map((ville) => <SelectItem key={ville.id} value={String(ville.id)}>{ville.name}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                </Field>
                            </div>
                        </FormSection>

                        <FormSection icon={UserRound} title="Compte du responsable" subtitle="Les identifiants utilisés pour vous connecter.">
                            <div className="grid gap-x-5 gap-y-6 sm:grid-cols-2">
                                <Field label="Nom complet" required error={form.errors.new_responsable_name}><Input required value={form.data.new_responsable_name} onChange={(e) => form.setData('new_responsable_name', e.target.value)} placeholder="Nom et prénoms" /></Field>
                                <Field label="Téléphone personnel" required error={form.errors.new_responsable_tel1}><PhoneInput required value={form.data.new_responsable_tel1} onChange={(value) => form.setData('new_responsable_tel1', value)} placeholder="07 00 00 00 00" /></Field>
                                <Field label="Email de connexion" required error={form.errors.new_responsable_email} className="sm:col-span-2"><Input required type="email" value={form.data.new_responsable_email} onChange={(e) => form.setData('new_responsable_email', e.target.value)} placeholder="responsable@agence.ci" /></Field>
                                <Field label="Mot de passe" required error={form.errors.new_responsable_password}><PasswordInput required value={form.data.new_responsable_password} onChange={(e) => form.setData('new_responsable_password', e.target.value)} placeholder="8 caractères minimum" /></Field>
                                <Field label="Confirmation" required error={form.errors.new_responsable_password_confirmation}><PasswordInput required value={form.data.new_responsable_password_confirmation} onChange={(e) => form.setData('new_responsable_password_confirmation', e.target.value)} placeholder="Confirmer le mot de passe" /></Field>
                            </div>
                        </FormSection>

                        <label className="mt-7 flex cursor-pointer items-start gap-3 text-xs leading-5 text-slate-500">
                            <input type="checkbox" required aria-required="true" checked={form.data.accept_terms} onChange={(e) => form.setData('accept_terms', e.target.checked)} className="mt-0.5 h-4 w-4 shrink-0 rounded accent-[#76c206]" />
                            <span>J’accepte les conditions d’utilisation et le traitement des informations de mon agence. <span className="font-bold text-red-600" aria-hidden="true">*</span></span>
                        </label>
                        {form.errors.accept_terms ? <p className="mt-2 text-xs font-medium text-red-600">{form.errors.accept_terms}</p> : null}

                        <div className="mt-7 flex flex-col gap-5 border-t border-[#0b1730]/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <p className="max-w-xs text-xs leading-5 text-slate-500">Vos informations sont utilisées uniquement pour créer et sécuriser votre espace.</p>
                            <button type="submit" disabled={form.processing} className="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#00559b] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#00457c] disabled:opacity-60">
                                {form.processing ? 'Création en cours…' : 'Créer mon agence'}
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </PublicLayout>
    );
}

function FormSection({ icon: Icon, title, subtitle, children }) {
    return <section className="mt-8 border-t border-[#0b1730]/10 pt-7"><div className="flex items-start gap-3"><Icon className="mt-0.5 h-5 w-5 shrink-0 text-[#67a900]" /><div><h3 className="text-base font-bold text-[#0b1730]">{title}</h3><p className="mt-1 text-sm text-slate-500">{subtitle}</p></div></div><div className="mt-6">{children}</div></section>;
}

function Field({ label, required = false, error, className = '', children }) {
    return <div className={`space-y-1.5 ${className}`}><label className="block text-sm font-medium text-[#0f172a]">{label}{required ? <span className="ml-1 font-bold text-red-600" aria-hidden="true">*</span> : null}</label>{children}{error ? <p className="text-xs text-[#b42318]">{error}</p> : null}</div>;
}

function PasswordInput(props) {
    const [isVisible, setIsVisible] = useState(false);

    return (
        <div className="relative">
            <LockKeyhole className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-[#00559b]" />
            <Input type={isVisible ? 'text' : 'password'} className="px-9" {...props} />
            <button
                type="button"
                onClick={() => setIsVisible((visible) => !visible)}
                className="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-[#5f7182] transition-colors hover:text-[#00559b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#00559b]"
                aria-label={isVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                title={isVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
            >
                {isVisible ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </button>
        </div>
    );
}

function RegistrationStep({ number, title, text }) {
    return <div className="flex gap-4 py-5"><span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#eaf4fb] text-xs font-bold text-[#00559b]">{number}</span><div><h3 className="text-sm font-bold text-[#0b1730]">{title}</h3><p className="mt-1 text-sm leading-6 text-slate-500">{text}</p></div></div>;
}
