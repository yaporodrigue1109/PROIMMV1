import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    ArrowUpRight,
    Building2,
    CheckCircle2,
    Clock3,
    Headphones,
    Mail,
    MapPin,
    Phone,
    Send,
} from 'lucide-react';

import PublicLayout from './PublicLayout';

const contactDetails = [
    {
        icon: Phone,
        label: 'Téléphone',
        value: '+225 07 00 00 00 00',
        href: 'tel:+2250700000000',
    },
    {
        icon: Mail,
        label: 'E-mail',
        value: 'contact@prosimmobilier.ci',
        href: 'mailto:contact@prosimmobilier.ci',
    },
    {
        icon: MapPin,
        label: 'Adresse',
        value: 'Abidjan, Côte d’Ivoire',
    },
    {
        icon: Clock3,
        label: 'Disponibilité',
        value: 'Lun – Ven, 08h00 – 18h00',
    },
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

            <main className="overflow-hidden bg-[#f5f8fc] text-[#111f3d]">
             

               

                {/* CONTACT FORM */}
                <section className="px-5 py-24 sm:px-6 lg:py-32">
                    <div className="mx-auto grid max-w-5xl gap-14 lg:grid-cols-[0.72fr_1.28fr] lg:gap-20">
                        <div>
                          
                            <h2 className="mt-5 text-4xl font-medium leading-[1.05] tracking-[-0.04em] text-[#0b1730] sm:text-5xl">
                                Comment pouvons-nous vous aider ?
                            </h2>
                        
                            <p className="mt-6 leading-7 text-slate-600">
                                Nous orienterons votre message vers la personne la
                                mieux placée pour vous répondre.
                            </p>

                            <div className="mt-10 divide-y divide-[#0b1730]/10 border-y border-[#0b1730]/10">
                                <ContactReason
                                    icon={Building2}
                                    title="Découvrir la plateforme"
                                    text="Demandez une présentation adaptée aux besoins de votre agence."
                                />
                                <ContactReason
                                    icon={Headphones}
                                    title="Obtenir de l’assistance"
                                    text="Précisez « Assistance » pour accélérer le traitement de votre demande."
                                />
                            </div>

                            <Link
                                href="/inscription-agence"
                                className="mt-8 inline-flex items-center gap-2 rounded-lg bg-[#76c206] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#66aa04]"
                            >
                                Créer mon agence
                                <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        </div>

                        <form
                            onSubmit={submit}
                            className="border-t border-[#0b1730]/15 pt-8"
                        >
                            <div className="flex items-start justify-between gap-5">
                                <div>
                                  
                                    <h2 className="mt-3 text-2xl font-medium tracking-[-0.025em] text-[#0b1730] sm:text-3xl">
                                        Envoyez-nous un message
                                    </h2>
                                </div>
                                <Send className="mt-1 hidden h-6 w-6 text-[#67a900] sm:block" />
                            </div>

                            {success ? (
                                <div className="mt-6 flex items-start gap-3 border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                                    <CheckCircle2 className="h-5 w-5 shrink-0" />
                                    {success}
                                </div>
                            ) : null}

                            <div className="mt-8 grid gap-x-5 gap-y-6 sm:grid-cols-2">
                                <Field
                                    label="Motif de la demande"
                                    error={form.errors.request_type}
                                    className="sm:col-span-2"
                                >
                                    <select
                                        value={form.data.request_type}
                                        onChange={(event) =>
                                            form.setData(
                                                'request_type',
                                                event.target.value,
                                            )
                                        }
                                        className={inputClass}
                                    >
                                        <option value="">Sélectionner un motif</option>
                                        <option value="demo">
                                            Demande de démonstration
                                        </option>
                                        <option value="inscription">
                                            Inscription ou abonnement
                                        </option>
                                        <option value="support">
                                            Assistance et support
                                        </option>
                                        <option value="partenariat">
                                            Partenariat
                                        </option>
                                        <option value="autre">Autre demande</option>
                                    </select>
                                </Field>

                                <Field
                                    label="Nom complet"
                                    error={form.errors.name}
                                >
                                    <input
                                        value={form.data.name}
                                        onChange={(event) =>
                                            form.setData('name', event.target.value)
                                        }
                                        className={inputClass}
                                        placeholder="Votre nom et vos prénoms"
                                    />
                                </Field>

                                <Field
                                    label="Téléphone"
                                    error={form.errors.phone}
                                >
                                    <input
                                        value={form.data.phone}
                                        onChange={(event) =>
                                            form.setData('phone', event.target.value)
                                        }
                                        className={inputClass}
                                        placeholder="+225 07 00 00 00 00"
                                    />
                                </Field>

                                <Field
                                    label="Adresse e-mail"
                                    error={form.errors.email}
                                >
                                    <input
                                        type="email"
                                        value={form.data.email}
                                        onChange={(event) =>
                                            form.setData('email', event.target.value)
                                        }
                                        className={inputClass}
                                        placeholder="vous@agence.ci"
                                    />
                                </Field>

                                <Field
                                    label="Objet"
                                    error={form.errors.subject}
                                >
                                    <input
                                        value={form.data.subject}
                                        onChange={(event) =>
                                            form.setData(
                                                'subject',
                                                event.target.value,
                                            )
                                        }
                                        className={inputClass}
                                        placeholder="Objet de votre demande"
                                    />
                                </Field>

                                <Field
                                    label="Votre message"
                                    error={form.errors.message}
                                    className="sm:col-span-2"
                                >
                                    <textarea
                                        rows="6"
                                        value={form.data.message}
                                        onChange={(event) =>
                                            form.setData(
                                                'message',
                                                event.target.value,
                                            )
                                        }
                                        className={`${inputClass} h-auto resize-y py-3`}
                                        placeholder="Expliquez-nous comment nous pouvons vous aider…"
                                    />
                                </Field>
                            </div>

                            <div className="mt-7 flex flex-col gap-5 border-t border-[#0b1730]/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p className="max-w-sm text-xs leading-5 text-slate-500">
                                    Ces informations seront uniquement utilisées
                                    pour répondre à votre demande.
                                </p>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#00559b] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#00457c] disabled:opacity-60"
                                >
                                    {form.processing
                                        ? 'Envoi en cours…'
                                        : 'Envoyer le message'}
                                    <Send className="h-4 w-4" />
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                 {/* CONTACT DETAILS */}
                <section className="border-y border-[#0b1730]/10 px-5 sm:px-6">
                    <div className="mx-auto grid max-w-5xl sm:grid-cols-2 lg:grid-cols-4">
                        {contactDetails.map(
                            ({ icon: Icon, label, value, href }, index) => {
                                const content = (
                                    <>
                                        <Icon className="h-5 w-5 text-[#67a900]" />
                                        <p className="mt-7 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">
                                            {label}
                                        </p>
                                        <p className="mt-2 text-sm font-semibold text-[#0b1730]">
                                            {value}
                                        </p>
                                    </>
                                );
                                const classes = `block min-h-40 border-[#0b1730]/10 px-0 py-7 transition sm:px-7 lg:py-8 ${
                                    index > 0 ? 'border-t sm:border-t-0' : ''
                                } ${
                                    index % 2 !== 0
                                        ? 'sm:border-l'
                                        : ''
                                } ${
                                    index > 1
                                        ? 'sm:border-t lg:border-t-0'
                                        : ''
                                } ${
                                    index > 0 ? 'lg:border-l' : ''
                                } ${href ? 'hover:bg-white/70' : ''}`;

                                return href ? (
                                    <a key={label} href={href} className={classes}>
                                        {content}
                                    </a>
                                ) : (
                                    <div key={label} className={classes}>
                                        {content}
                                    </div>
                                );
                            },
                        )}
                    </div>
                </section>
            </main>
        </PublicLayout>
    );
}

const inputClass =
    'h-12 w-full rounded-lg border border-[#0b1730]/15 bg-white/70 px-4 text-sm text-[#111f3d] outline-none transition placeholder:text-slate-400 focus:border-[#00559b] focus:bg-white focus:ring-4 focus:ring-[#00559b]/10';

function Field({ label, error, className = '', children }) {
    return (
        <label className={`block ${className}`}>
            <span className="mb-2 block text-sm font-semibold text-[#334155]">
                {label}
            </span>
            {children}
            {error ? (
                <span className="mt-1.5 block text-xs font-medium text-red-600">
                    {error}
                </span>
            ) : null}
        </label>
    );
}

function ContactReason({ icon: Icon, title, text }) {
    return (
        <div className="flex gap-4 py-5">
            <Icon className="mt-0.5 h-5 w-5 shrink-0 text-[#67a900]" />
            <div>
                <h3 className="text-sm font-bold text-[#0b1730]">{title}</h3>
                <p className="mt-1 text-sm leading-6 text-slate-500">{text}</p>
            </div>
        </div>
    );
}
