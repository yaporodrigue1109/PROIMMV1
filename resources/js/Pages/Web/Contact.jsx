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

import { Input } from '../../components/ui/input';
import { PhoneInput } from '../../components/ui/phone-input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../components/ui/select';
import PublicLayout from './PublicLayout';

export default function Contact() {
    const pageProps = usePage().props;
    const success = pageProps.flash?.success;
    const config = pageProps.siteConfig ?? {};
    const contactDetails = [
        config.phone && { icon: Phone, label: 'Téléphone', value: config.phone, href: `tel:${config.phone}` },
        config.email && { icon: Mail, label: 'E-mail', value: config.email, href: `mailto:${config.email}` },
        config.address && { icon: MapPin, label: 'Adresse', value: config.address },
        { icon: Clock3, label: 'Disponibilité', value: 'Lun – Ven, 08h00 – 18h00' },
    ].filter(Boolean);
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
                                    required
                                    error={form.errors.request_type}
                                    className="sm:col-span-2"
                                >
                                    <Select
                                        required
                                        value={form.data.request_type}
                                        onValueChange={(value) => form.setData('request_type', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionner un motif" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="demo">Demande de démonstration</SelectItem>
                                            <SelectItem value="inscription">Inscription ou abonnement</SelectItem>
                                            <SelectItem value="support">Assistance et support</SelectItem>
                                            <SelectItem value="partenariat">Partenariat</SelectItem>
                                            <SelectItem value="autre">Autre demande</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field
                                    label="Nom complet"
                                    required
                                    error={form.errors.name}
                                >
                                    <Input
                                        required
                                        value={form.data.name}
                                        onChange={(event) =>
                                            form.setData('name', event.target.value)
                                        }
                                        placeholder="Votre nom et vos prénoms"
                                    />
                                </Field>

                                <Field
                                    label="Téléphone"
                                    error={form.errors.phone}
                                >
                                    <PhoneInput
                                        value={form.data.phone}
                                        onChange={(value) => form.setData('phone', value)}
                                        placeholder="07 00 00 00 00"
                                    />
                                </Field>

                                <Field
                                    label="Adresse e-mail"
                                    required
                                    error={form.errors.email}
                                >
                                    <Input
                                        required
                                        type="email"
                                        value={form.data.email}
                                        onChange={(event) =>
                                            form.setData('email', event.target.value)
                                        }
                                        placeholder="vous@agence.ci"
                                    />
                                </Field>

                                <Field
                                    label="Objet"
                                    required
                                    error={form.errors.subject}
                                >
                                    <Input
                                        required
                                        value={form.data.subject}
                                        onChange={(event) =>
                                            form.setData(
                                                'subject',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="Objet de votre demande"
                                    />
                                </Field>

                                <Field
                                    label="Votre message"
                                    required
                                    error={form.errors.message}
                                    className="sm:col-span-2"
                                >
                                    <textarea
                                        required
                                        rows="6"
                                        value={form.data.message}
                                        onChange={(event) =>
                                            form.setData(
                                                'message',
                                                event.target.value,
                                            )
                                        }
                                        className={textareaClass}
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

const textareaClass =
    'flex min-h-32 w-full resize-y rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] shadow-sm ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

function Field({ label, required = false, error, className = '', children }) {
    return (
        <div className={`space-y-1.5 ${className}`}>
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? (
                    <span className="ml-1 font-bold text-[#b42318]" aria-hidden="true">*</span>
                ) : null}
            </label>
            {children}
            {error ? (
                <span className="mt-1.5 block text-xs font-medium text-red-600">
                    {error}
                </span>
            ) : null}
        </div>
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
