import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowRight, Building2, CheckCircle2, Clock3, Headphones, Mail, MapPin, MessageCircle, Phone, Send } from 'lucide-react';

import PublicLayout from './PublicLayout';

const contactCards = [
    { icon: Phone, label: 'Téléphone', value: '+225 07 00 00 00 00', href: 'tel:+2250700000000' },
    { icon: Mail, label: 'E-mail', value: 'contact@prosimmobilier.ci', href: 'mailto:contact@prosimmobilier.ci' },
    { icon: MapPin, label: 'Adresse', value: 'Abidjan, Côte d’Ivoire' },
    { icon: Clock3, label: 'Disponibilité', value: 'Lun – Ven, 08h00 – 18h00' },
];

export default function Contact() {
    const success = usePage().props.flash?.success;
    const form = useForm({
        request_type: '',
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/contact', {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <PublicLayout>
            <Head title="Contact" />

            <main>
                <section className="relative overflow-hidden bg-[#111f3d] px-5 py-20 text-white lg:py-24">
                    <div className="absolute -right-28 -top-40 h-[28rem] w-[28rem] rounded-full bg-[#76c206]/15" />
                    <div className="absolute -bottom-52 -left-28 h-[30rem] w-[30rem] rounded-full bg-[#00559b]/35" />
                    <div className="relative mx-auto max-w-7xl">
                        <div className="max-w-3xl">
                            <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-bold text-[#9bd63f]"><MessageCircle className="h-4 w-4" /> Nous sommes à votre écoute</span>
                            <h1 className="mt-6 text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">Parlons de votre agence et de vos objectifs</h1>
                            <p className="mt-6 max-w-2xl text-lg leading-8 text-white/70">Une question sur l’inscription, une démonstration ou un besoin d’assistance ? Notre équipe vous accompagne et vous répond dans les meilleurs délais.</p>
                        </div>
                    </div>
                </section>

                <section className="relative z-10 -mt-8 px-5">
                    <div className="mx-auto grid max-w-7xl gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {contactCards.map(({ icon: Icon, label, value, href }) => {
                            const content = <><span className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#00559b]/10 text-[#00559b]"><Icon className="h-5 w-5" /></span><div><p className="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-400">{label}</p><p className="mt-1 text-sm font-bold text-[#111f3d]">{value}</p></div></>;
                            return href ? <a key={label} href={href} className="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-lg shadow-slate-900/5 transition hover:-translate-y-1">{content}</a> : <div key={label} className="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-lg shadow-slate-900/5">{content}</div>;
                        })}
                    </div>
                </section>

                <section className="px-5 py-20">
                    <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.75fr_1.25fr]">
                        <div>
                            <p className="text-sm font-extrabold uppercase tracking-[0.2em] text-[#76c206]">Comment pouvons-nous vous aider ?</p>
                            <h2 className="mt-3 text-3xl font-extrabold text-[#111f3d] sm:text-4xl">Un interlocuteur pour chaque besoin</h2>
                            <p className="mt-5 leading-7 text-slate-600">Décrivez-nous votre situation. Nous orienterons votre demande vers la personne la mieux placée pour vous répondre.</p>

                            <div className="mt-8 space-y-4">
                                <Benefit icon={Building2} title="Vous souhaitez rejoindre la plateforme ?" text="Créez directement votre agence et commencez votre souscription en ligne." />
                                <Benefit icon={Headphones} title="Vous utilisez déjà Pros Immobilier ?" text="Indiquez « Assistance » dans le formulaire pour faciliter le traitement de votre demande." />
                            </div>

                            <Link href="/inscription-agence" className="mt-8 inline-flex items-center gap-2 rounded-full bg-[#76c206] px-6 py-3.5 text-sm font-extrabold text-white transition hover:bg-[#66aa04]">Créer mon agence <ArrowRight className="h-4 w-4" /></Link>
                        </div>

                        <form onSubmit={submit} className="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-[0_24px_70px_rgba(17,31,61,0.10)] sm:p-9">
                            <div className="flex items-start justify-between gap-5">
                                <div><p className="text-sm font-bold text-[#00559b]">Formulaire de contact</p><h2 className="mt-1 text-2xl font-extrabold text-[#111f3d]">Envoyez-nous un message</h2></div>
                                <span className="hidden h-12 w-12 items-center justify-center rounded-2xl bg-[#00559b] text-white sm:flex"><Send className="h-5 w-5" /></span>
                            </div>

                            {success ? <div className="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800"><CheckCircle2 className="h-5 w-5 shrink-0" /> {success}</div> : null}

                            <div className="mt-7 grid gap-5 sm:grid-cols-2">
                                <Field label="Motif de la demande" error={form.errors.request_type} className="sm:col-span-2">
                                    <select value={form.data.request_type} onChange={(e) => form.setData('request_type', e.target.value)} className={inputClass}>
                                        <option value="">Sélectionner un motif</option>
                                        <option value="demo">Demande de démonstration</option>
                                        <option value="inscription">Inscription ou abonnement</option>
                                        <option value="support">Assistance et support</option>
                                        <option value="partenariat">Partenariat</option>
                                        <option value="autre">Autre demande</option>
                                    </select>
                                </Field>
                                <Field label="Nom complet" error={form.errors.name}><input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className={inputClass} placeholder="Votre nom et vos prénoms" /></Field>
                                <Field label="Téléphone" error={form.errors.phone}><input value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} className={inputClass} placeholder="+225 07 00 00 00 00" /></Field>
                                <Field label="Adresse e-mail" error={form.errors.email}><input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className={inputClass} placeholder="vous@agence.ci" /></Field>
                                <Field label="Objet" error={form.errors.subject}><input value={form.data.subject} onChange={(e) => form.setData('subject', e.target.value)} className={inputClass} placeholder="Objet de votre demande" /></Field>
                                <Field label="Votre message" error={form.errors.message} className="sm:col-span-2">
                                    <textarea rows="6" value={form.data.message} onChange={(e) => form.setData('message', e.target.value)} className={`${inputClass} h-auto resize-y py-3`} placeholder="Expliquez-nous comment nous pouvons vous aider…" />
                                </Field>
                            </div>

                            <div className="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <p className="max-w-sm text-xs leading-5 text-slate-400">En envoyant ce formulaire, vous acceptez que nous utilisions ces informations pour répondre à votre demande.</p>
                                <button disabled={form.processing} className="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-[#00559b] px-7 py-3.5 text-sm font-extrabold text-white transition hover:bg-[#00457c] disabled:opacity-60">{form.processing ? 'Envoi en cours…' : 'Envoyer le message'} <Send className="h-4 w-4" /></button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </PublicLayout>
    );
}

const inputClass = 'h-12 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 text-sm text-[#111f3d] outline-none transition placeholder:text-slate-400 focus:border-[#00559b] focus:bg-white focus:ring-4 focus:ring-[#00559b]/10';

function Field({ label, error, className = '', children }) {
    return <label className={`block ${className}`}><span className="mb-2 block text-sm font-bold text-[#334155]">{label}</span>{children}{error ? <span className="mt-1.5 block text-xs font-medium text-red-600">{error}</span> : null}</label>;
}

function Benefit({ icon: Icon, title, text }) {
    return <div className="flex gap-4 rounded-2xl border border-slate-100 bg-[#f8fbfe] p-5"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#00559b] shadow-sm"><Icon className="h-5 w-5" /></span><div><h3 className="text-sm font-extrabold text-[#111f3d]">{title}</h3><p className="mt-1 text-sm leading-6 text-slate-500">{text}</p></div></div>;
}
