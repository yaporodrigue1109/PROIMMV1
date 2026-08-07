import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, ArrowUpRight, BarChart3, Headphones, Home as HomeIcon, KeyRound, MapPin, Search, ShieldCheck, Star, UsersRound, WalletCards } from 'lucide-react';
import { useState } from 'react';
import PropertyCards from './PropertyCards';
import PublicLayout from './PublicLayout';

const heroImage = 'https://images.unsplash.com/photo-1757359056339-22968344cce6?auto=format&fit=crop&q=85&w=2200';

const services = [
    { icon: KeyRound, title: 'Gestion locative', text: 'Loyers, contrats, locataires et reversements suivis avec précision.' },
    { icon: HomeIcon, title: 'Location & vente', text: 'Une sélection de biens fiables pour habiter ou investir sereinement.' },
    { icon: BarChart3, title: 'Pilotage immobilier', text: 'Des indicateurs clairs pour prendre de meilleures décisions patrimoniales.' },
];

const advantages = [
    { icon: ShieldCheck, label: 'Transactions sécurisées', text: 'Chaque étape est encadrée et documentée.' },
    { icon: UsersRound, label: 'Conseillers dédiés', text: 'Un interlocuteur unique du début à la fin.' },
    { icon: WalletCards, label: 'Suivi financier clair', text: 'Reversements et échéances toujours lisibles.' },
    { icon: Headphones, label: 'Support réactif', text: 'Une équipe joignable quand vous en avez besoin.' },
];

const testimonials = [
    { name: 'Awa K.', role: 'Propriétaire', quote: 'Une équipe disponible et des reversements toujours clairs. Je suis mon patrimoine sans stress.' },
    { name: 'Jean-Marc D.', role: 'Investisseur', quote: 'Le suivi est précis et les tableaux de bord me permettent de décider rapidement.' },
    { name: 'Fatou B.', role: 'Locataire', quote: 'Une recherche simple, une visite rapide et un accompagnement jusqu’à la remise des clés.' },
];

export default function Home({ properties = [] }) {
    const [mode, setMode] = useState('location');
    const [search, setSearch] = useState('');

    const submitSearch = (event) => {
        event.preventDefault();
        router.get('/biens', { mode, search: search || undefined });
    };

    return (
        <PublicLayout>
            <Head title="Accueil" />

            {/* HERO */}
            <section className="relative bg-[#0c1a35]">
                <img src={heroImage || "/placeholder.svg"} alt="Maison contemporaine" className="absolute inset-0 h-full w-full object-cover" />
                <div className="absolute inset-0 bg-gradient-to-r from-[#0c1a35] via-[#0c1a35]/85 to-[#0c1a35]/30" />
                <div className="relative mx-auto max-w-7xl px-6">

                    <div className="max-w-3xl py-24 sm:py-32">
                        <h1 className="mt-5 font-sans text-4xl font-medium leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl text-balance">
                            Acheter, louer ou confier la gestion de votre bien.
                        </h1>
                        <p className="mt-6 max-w-xl text-base leading-relaxed text-white/70">
                            L’accompagnement humain d’une agence associé à des outils qui sécurisent chaque opération, du premier contact au suivi quotidien.
                        </p>
                        <div className="mt-9 flex flex-wrap items-center gap-6">
                            <Link href="/biens" className="inline-flex items-center gap-2 rounded-[8px] bg-[#76c206] px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-[#66aa04]">
                                Découvrir nos biens <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link href="/contact" className="inline-flex items-center gap-2 text-sm font-semibold text-white/90 transition-colors hover:text-white">
                                Parler à un conseiller <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* SEARCH BAR */}
            <section className="border-b border-slate-200 bg-white">
                <div className="mx-auto max-w-6xl px-6 py-6">
                    <form onSubmit={submitSearch} className="flex flex-col gap-4 md:flex-row md:items-center">
                        <div className="flex gap-6">
                            {[['location', 'À louer'], ['vente', 'À vendre']].map(([value, label]) => (
                                <button
                                    key={value}
                                    type="button"
                                    onClick={() => setMode(value)}
                                    className={`relative pb-1 text-sm font-semibold transition-colors ${mode === value ? 'text-[#00559b]' : 'text-slate-400 hover:text-slate-600'}`}
                                >
                                    {label}
                                    {mode === value && <span className="absolute -bottom-px left-0 h-0.5 w-full bg-[#76c206]" />}
                                </button>
                            ))}
                        </div>
                        <div className="hidden h-8 w-px bg-slate-200 md:block" />
                        <label className="flex flex-1 items-center gap-3 rounded-[8px] border border-slate-200 bg-slate-50 px-4 py-1.5 transition-colors focus-within:border-[#00559b]/50 focus-within:bg-white">
                            <MapPin className="h-5 w-5 shrink-0 text-[#76c206]" />
                            <input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Ville, quartier ou référence du bien"
                                className="h-9 w-full bg-transparent text-sm outline-none placeholder:text-slate-400"
                            />
                        </label>
                        <button className="inline-flex items-center justify-center gap-2 rounded-[8px] bg-[#00559b] px-7 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#004a87]">
                            <Search className="h-4 w-4" /> Rechercher
                        </button>
                    </form>
                </div>
            </section>

            {/* ABOUT + SERVICES */}
            <section className="mx-auto max-w-7xl px-6 py-20 lg:py-28">
                <div className="grid gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
                    <div>
                        <h2 className="font-sans text-3xl font-medium leading-tight text-[#111f3d] sm:text-4xl text-balance">
                            Une expertise locale, une gestion moderne.
                        </h2>
                        <p className="mt-6 leading-relaxed text-slate-600">
                            Pros Immobilier réunit l’accompagnement d’une agence de proximité et la précision d’une plateforme digitale pour sécuriser chaque opération.
                        </p>
                        <div className="mt-10 flex divide-x divide-slate-200 border-y border-slate-200">
                            <div className="pr-8 py-5">
                                <p className="font-sans text-4xl text-[#00559b]">10+</p>
                                <p className="mt-1 text-sm text-slate-500">Années d’expertise</p>
                            </div>
                            <div className="px-8 py-5">
                                <p className="font-sans text-4xl text-[#00559b]">98%</p>
                                <p className="mt-1 text-sm text-slate-500">Clients satisfaits</p>
                            </div>
                        </div>
                    </div>

                    <div className="divide-y divide-slate-200 border-t border-slate-200">
                        {services.map(({ icon: Icon, title, text }, index) => (
                            <article key={title} className="group flex items-start gap-6 py-7">
                                <span className="font-sans text-lg text-slate-300">0{index + 1}</span>
                                <span className="flex h-11 w-11 shrink-0 items-center justify-center bg-[#eaf4fb] text-[#00559b]">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <div className="flex-1">
                                    <h3 className="text-lg font-semibold text-[#111f3d]">{title}</h3>
                                    <p className="mt-2 leading-relaxed text-slate-500">{text}</p>
                                </div>
                                <Link href="/contact" className="mt-1 hidden text-slate-300 transition-colors group-hover:text-[#00559b] sm:block">
                                    <ArrowUpRight className="h-5 w-5" />
                                </Link>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            {/* PROPERTIES */}
            <section className="border-y border-slate-200 bg-[#f6f7f9] py-20">
                <div className="mx-auto max-w-7xl px-6">
                    <div className="flex flex-col justify-between gap-5 border-b border-slate-200 pb-8 md:flex-row md:items-end">
                        <div>
                            <h2 className="font-sans text-3xl font-medium text-[#111f3d] sm:text-4xl">Des biens qui vous ressemblent</h2>
                            <p className="mt-3 max-w-xl leading-relaxed text-slate-500">Nos dernières opportunités disponibles à la location et à la vente.</p>
                        </div>
                        <Link href="/biens" className="inline-flex items-center gap-2 text-sm font-semibold text-[#00559b] hover:text-[#004a87]">
                            Voir tous les biens <ArrowRight className="h-4 w-4" />
                        </Link>
                    </div>
                    <div className="mt-10">
                        <PropertyCards properties={properties} />
                    </div>
                </div>
            </section>

            {/* WHY US */}
            <section className="bg-[#0c1a35] py-20 text-white lg:py-28">
                <div className="mx-auto max-w-7xl px-6">
                    <div className="max-w-2xl">
                        <h2 className="font-sans text-3xl font-medium leading-tight sm:text-4xl text-balance">
                            Un accompagnement pensé pour votre tranquillité.
                        </h2>
                        <p className="mt-5 leading-relaxed text-white/60">
                            De la première visite au suivi quotidien, nous protégeons vos intérêts avec transparence et réactivité.
                        </p>
                    </div>
                    <div className="mt-12 grid gap-px overflow-hidden border border-white/10 bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                        {advantages.map(({ icon: Icon, label, text }) => (
                            <div key={label} className="bg-[#0c1a35] p-7">
                                <span className="flex h-11 w-11 items-center justify-center bg-[#76c206]">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <h3 className="mt-5 font-semibold">{label}</h3>
                                <p className="mt-2 text-sm leading-relaxed text-white/55">{text}</p>
                            </div>
                        ))}
                    </div>
                    <div className="mt-12 grid grid-cols-2 gap-8 border-t border-white/10 pt-10 md:grid-cols-4">
                        {[['500+', 'Biens gérés'], ['350+', 'Locataires'], ['120+', 'Propriétaires'], ['4.9/5', 'Note moyenne']].map(([value, label]) => (
                            <div key={label}>
                                <p className="font-sans text-3xl text-[#9fdd42]">{value}</p>
                                <p className="mt-1 text-sm text-white/55">{label}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* TESTIMONIALS */}
            <section className="mx-auto max-w-7xl px-6 py-20 lg:py-28">
                <h2 className="max-w-md font-sans text-3xl font-medium text-[#111f3d] sm:text-4xl">L’expérience de nos clients</h2>
                <div className="mt-12 grid gap-px overflow-hidden border border-slate-200 bg-slate-200 md:grid-cols-3">
                    {testimonials.map(({ name, role, quote }) => (
                        <article key={name} className="flex flex-col bg-white p-8">
                            <div className="flex gap-0.5 text-[#f6b51b]">
                                {Array.from({ length: 5 }).map((_, i) => <Star key={i} className="h-4 w-4 fill-current" />)}
                            </div>
                            <p className="mt-5 flex-1 leading-relaxed text-slate-600">“{quote}”</p>
                            <div className="mt-6 flex items-center gap-3 border-t border-slate-100 pt-5">
                                <span className="flex h-10 w-10 items-center justify-center rounded-full bg-[#eaf4fb] font-semibold text-[#00559b]">{name[0]}</span>
                                <div>
                                    <p className="font-semibold text-[#111f3d]">{name}</p>
                                    <p className="text-xs text-slate-400">{role}</p>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            </section>

            {/* CTA */}
            <section className="border-t border-slate-200 bg-[#00559b] text-white">
                <div className="mx-auto flex max-w-7xl flex-col justify-between gap-8 px-6 py-16 lg:flex-row lg:items-center">
                    <div>
                        <p className="text-sm font-semibold text-[#9fdd42]">Vous avez un projet immobilier ?</p>
                        <h2 className="mt-2 max-w-2xl font-sans text-3xl font-medium leading-tight text-balance">
                            Parlons-en et construisons la solution qui vous convient.
                        </h2>
                    </div>
                    <Link href="/contact" className="inline-flex shrink-0 items-center justify-center gap-2 rounded-[8px] bg-[#76c206] px-7 py-4 text-sm font-semibold transition-colors hover:bg-[#66aa04]">
                        Prendre rendez-vous <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}
