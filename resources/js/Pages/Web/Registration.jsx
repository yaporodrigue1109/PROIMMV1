import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Building2, CheckCircle2, LockKeyhole, UserRound } from 'lucide-react';
import { useMemo } from 'react';

import PublicLayout from './PublicLayout';

export default function Registration({ regions = [], villes = [] }) {
    const form = useForm({
        name: '',
        adresse: '',
        tel1: '',
        email1: '',
        region: '',
        ville_id: '',
        new_responsable_name: '',
        new_responsable_email: '',
        new_responsable_tel1: '',
        new_responsable_password: '',
        new_responsable_password_confirmation: '',
        accept_terms: false,
    });

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
            <main className="bg-[#f4f9fd] px-5 py-14 lg:py-20">
                <div className="mx-auto max-w-6xl">
                    <div className="mx-auto max-w-3xl text-center">
                        <span className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-[#00559b] shadow-sm"><Building2 className="h-4 w-4 text-[#76c206]" /> Inscription agence</span>
                        <h1 className="mt-5 text-4xl font-extrabold tracking-tight text-[#111f3d] sm:text-5xl">Créez votre espace Pros Immobilier</h1>
                        <p className="mt-5 text-lg leading-8 text-slate-600">Renseignez votre agence et votre compte responsable. Vous serez ensuite dirigé vers le choix de votre abonnement.</p>
                    </div>

                    <form onSubmit={submit} className="mt-12 grid gap-7 lg:grid-cols-[minmax(0,1fr)_310px]">
                        <div className="space-y-7">
                            <FormSection icon={Building2} title="Informations de l’agence" subtitle="Ces informations permettront d’identifier votre agence.">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <Field label="Nom de l’agence" error={form.errors.name}><input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className={inputClass} placeholder="Ex. Agence Horizon" /></Field>
                                    <Field label="Téléphone" error={form.errors.tel1}><input value={form.data.tel1} onChange={(e) => form.setData('tel1', e.target.value)} className={inputClass} placeholder="+225 07 00 00 00 00" /></Field>
                                    <Field label="Email de l’agence" error={form.errors.email1}><input type="email" value={form.data.email1} onChange={(e) => form.setData('email1', e.target.value)} className={inputClass} placeholder="contact@agence.ci" /></Field>
                                    <Field label="Adresse" error={form.errors.adresse}><input value={form.data.adresse} onChange={(e) => form.setData('adresse', e.target.value)} className={inputClass} placeholder="Cocody, Abidjan" /></Field>
                                    <Field label="Région" error={form.errors.region}>
                                        <select value={form.data.region} onChange={(e) => { form.setData('region', e.target.value); form.setData('ville_id', ''); }} className={inputClass}>
                                            <option value="">Sélectionner une région</option>
                                            {regions.map((region) => <option key={region.id} value={region.id}>{region.name}</option>)}
                                        </select>
                                    </Field>
                                    <Field label="Ville" error={form.errors.ville_id}>
                                        <select value={form.data.ville_id} onChange={(e) => form.setData('ville_id', e.target.value)} disabled={!form.data.region} className={`${inputClass} disabled:bg-slate-100`}>
                                            <option value="">Sélectionner une ville</option>
                                            {availableCities.map((ville) => <option key={ville.id} value={ville.id}>{ville.name}</option>)}
                                        </select>
                                    </Field>
                                </div>
                            </FormSection>

                            <FormSection icon={UserRound} title="Compte du responsable" subtitle="Ces identifiants serviront à vous connecter à l’application.">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <Field label="Nom complet" error={form.errors.new_responsable_name}><input value={form.data.new_responsable_name} onChange={(e) => form.setData('new_responsable_name', e.target.value)} className={inputClass} placeholder="Nom et prénoms" /></Field>
                                    <Field label="Téléphone personnel" error={form.errors.new_responsable_tel1}><input value={form.data.new_responsable_tel1} onChange={(e) => form.setData('new_responsable_tel1', e.target.value)} className={inputClass} placeholder="+225 07 00 00 00 00" /></Field>
                                    <Field label="Email de connexion" error={form.errors.new_responsable_email}><input type="email" value={form.data.new_responsable_email} onChange={(e) => form.setData('new_responsable_email', e.target.value)} className={inputClass} placeholder="responsable@agence.ci" /></Field>
                                    <div className="hidden sm:block" />
                                    <Field label="Mot de passe" error={form.errors.new_responsable_password}><input type="password" value={form.data.new_responsable_password} onChange={(e) => form.setData('new_responsable_password', e.target.value)} className={inputClass} placeholder="8 caractères minimum" /></Field>
                                    <Field label="Confirmation" error={form.errors.new_responsable_password_confirmation}><input type="password" value={form.data.new_responsable_password_confirmation} onChange={(e) => form.setData('new_responsable_password_confirmation', e.target.value)} className={inputClass} placeholder="Confirmer le mot de passe" /></Field>
                                </div>
                            </FormSection>
                        </div>

                        <aside className="lg:sticky lg:top-28 lg:self-start">
                            <div className="rounded-[1.75rem] bg-[#111f3d] p-7 text-white shadow-xl">
                                <LockKeyhole className="h-9 w-9 text-[#76c206]" />
                                <h2 className="mt-5 text-xl font-extrabold">Votre espace en 3 étapes</h2>
                                <div className="mt-6 space-y-4 text-sm text-white/70">
                                    {['Créez votre agence', 'Connectez-vous à votre espace', 'Choisissez votre abonnement'].map((item) => <p key={item} className="flex items-center gap-3"><CheckCircle2 className="h-5 w-5 shrink-0 text-[#76c206]" /> {item}</p>)}
                                </div>
                                <label className="mt-7 flex cursor-pointer items-start gap-3 border-t border-white/10 pt-6 text-xs leading-5 text-white/65">
                                    <input type="checkbox" checked={form.data.accept_terms} onChange={(e) => form.setData('accept_terms', e.target.checked)} className="mt-1 h-4 w-4 rounded accent-[#76c206]" />
                                    J’accepte les conditions d’utilisation et le traitement des informations de mon agence.
                                </label>
                                {form.errors.accept_terms ? <p className="mt-2 text-xs text-red-300">{form.errors.accept_terms}</p> : null}
                                <button type="submit" disabled={form.processing} className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#76c206] px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-[#66aa04] disabled:opacity-60">
                                    {form.processing ? 'Création en cours…' : 'Créer mon agence'} <ArrowRight className="h-4 w-4" />
                                </button>
                                <p className="mt-5 text-center text-xs text-white/55">Déjà inscrit ? <Link href="/agence/login" className="font-bold text-white hover:text-[#76c206]">Se connecter</Link></p>
                            </div>
                        </aside>
                    </form>
                </div>
            </main>
        </PublicLayout>
    );
}

const inputClass = 'h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#111f3d] outline-none transition placeholder:text-slate-400 focus:border-[#00559b] focus:ring-4 focus:ring-[#00559b]/10';

function FormSection({ icon: Icon, title, subtitle, children }) {
    return <section className="rounded-[1.75rem] border border-slate-100 bg-white p-6 shadow-sm sm:p-8"><div className="flex items-start gap-4"><span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#00559b]/10 text-[#00559b]"><Icon className="h-5 w-5" /></span><div><h2 className="text-xl font-extrabold text-[#111f3d]">{title}</h2><p className="mt-1 text-sm text-slate-500">{subtitle}</p></div></div><div className="mt-7">{children}</div></section>;
}

function Field({ label, error, children }) {
    return <label className="block"><span className="mb-2 block text-sm font-bold text-[#334155]">{label}</span>{children}{error ? <span className="mt-1.5 block text-xs font-medium text-red-600">{error}</span> : null}</label>;
}
