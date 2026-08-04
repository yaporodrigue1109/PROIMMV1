import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, BadgeCheck, BarChart3, Building2, Headphones, Home as HomeIcon, KeyRound, MapPin, Search, ShieldCheck, Star, UsersRound, WalletCards } from 'lucide-react';
import { useState } from 'react';
import PropertyCards from './PropertyCards';
import PublicLayout from './PublicLayout';

const heroImage = 'https://images.unsplash.com/photo-1757359056339-22968344cce6?auto=format&fit=crop&q=85&w=2200';

const services = [
    { icon: KeyRound, title: 'Gestion locative', text: 'Loyers, contrats, locataires et reversements suivis avec précision.' },
    { icon: HomeIcon, title: 'Location & vente', text: 'Une sélection de biens fiables pour habiter ou investir sereinement.' },
    { icon: BarChart3, title: 'Pilotage immobilier', text: 'Des indicateurs clairs pour prendre de meilleures décisions patrimoniales.' },
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
            <section className="relative min-h-[690px] overflow-hidden bg-[#111f3d]">
                <img src={heroImage} alt="Maison contemporaine" className="absolute inset-0 h-full w-full object-cover" />
                <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(9,24,52,0.94)_0%,rgba(10,34,67,0.72)_47%,rgba(10,34,67,0.22)_100%)]" />
                <div className="relative mx-auto flex min-h-[690px] max-w-7xl items-center px-6 pb-32 pt-20">
                    <div className="max-w-3xl text-white">
                        <div className="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] backdrop-blur"><span className="h-2 w-2 rounded-full bg-[#76c206]" /> L’immobilier en toute confiance</div>
                        <h1 className="mt-6 text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-7xl">Votre projet immobilier<br /><span className="text-[#76c206]">commence ici.</span></h1>
                        <p className="mt-6 max-w-2xl text-base leading-8 text-white/75 sm:text-lg">Achetez, louez ou confiez-nous la gestion de votre bien. Notre expertise et nos outils vous accompagnent à chaque étape.</p>
                        <div className="mt-8 flex flex-wrap gap-4"><Link href="/biens" className="inline-flex items-center gap-2 rounded-full bg-[#76c206] px-6 py-3.5 text-sm font-bold text-white shadow-xl shadow-black/15 transition hover:-translate-y-0.5">Découvrir nos biens <ArrowRight className="h-4 w-4" /></Link><Link href="/contact" className="rounded-full border border-white/40 bg-white/10 px-6 py-3.5 text-sm font-bold backdrop-blur transition hover:bg-white hover:text-[#111f3d]">Parler à un conseiller</Link></div>
                    </div>
                </div>
            </section>

            <div className="relative z-10 mx-auto -mt-24 max-w-6xl px-5">
                <form onSubmit={submitSearch} className="rounded-3xl bg-white p-4 shadow-[0_25px_70px_rgba(17,31,61,0.18)] sm:p-6">
                    <div className="mb-5 flex gap-2">{[['location','À louer'],['vente','À vendre']].map(([value,label]) => <button key={value} type="button" onClick={() => setMode(value)} className={`rounded-full px-5 py-2 text-sm font-bold transition ${mode === value ? 'bg-[#00559b] text-white' : 'bg-slate-100 text-slate-500'}`}>{label}</button>)}</div>
                    <div className="grid gap-3 md:grid-cols-[1fr_auto]">
                        <label className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4"><MapPin className="h-5 w-5 text-[#76c206]" /><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Ville, quartier ou référence du bien" className="h-14 w-full bg-transparent text-sm outline-none placeholder:text-slate-400" /></label>
                        <button className="inline-flex h-14 items-center justify-center gap-2 rounded-2xl bg-[#76c206] px-8 text-sm font-bold text-white transition hover:bg-[#66aa04]"><Search className="h-4 w-4" /> Rechercher</button>
                    </div>
                </form>
            </div>

            <section className="mx-auto max-w-7xl px-6 pb-20 pt-28">
                <div className="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <div className="relative min-h-[450px] overflow-hidden rounded-[2rem] bg-[#eaf4fb] p-8">
                        <div className="absolute -bottom-10 -right-10 h-52 w-52 rounded-full bg-[#76c206]/20" />
                        <div className="relative flex h-full flex-col justify-between">
                            <div><p className="text-xs font-bold uppercase tracking-[0.2em] text-[#00559b]">À propos de nous</p><h2 className="mt-4 text-3xl font-extrabold leading-tight text-[#111f3d] sm:text-4xl">Une expertise locale, une gestion moderne.</h2><p className="mt-5 leading-7 text-slate-600">Pros Immobilier réunit l’accompagnement humain d’une agence et la puissance d’une plateforme digitale pour sécuriser chaque opération.</p></div>
                            <div className="mt-10 grid grid-cols-2 gap-4"><div className="rounded-2xl bg-white p-5 shadow-sm"><p className="text-3xl font-extrabold text-[#00559b]">10+</p><p className="mt-1 text-xs text-slate-500">Années d’expertise</p></div><div className="rounded-2xl bg-[#111f3d] p-5 text-white"><p className="text-3xl font-extrabold text-[#76c206]">98%</p><p className="mt-1 text-xs text-white/60">Clients satisfaits</p></div></div>
                        </div>
                    </div>
                    <div className="grid gap-5 sm:grid-cols-2">{services.map(({ icon: Icon, title, text }, index) => <article key={title} className={`group rounded-3xl border border-slate-100 p-7 shadow-[0_14px_45px_rgba(17,31,61,0.07)] transition hover:-translate-y-1 hover:border-[#76c206]/40 ${index === 2 ? 'sm:col-span-2' : ''}`}><span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#eaf4fb] text-[#00559b] transition group-hover:bg-[#76c206] group-hover:text-white"><Icon className="h-7 w-7" /></span><h3 className="mt-5 text-xl font-bold text-[#111f3d]">{title}</h3><p className="mt-3 leading-7 text-slate-500">{text}</p><Link href="/contact" className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#00559b]">En savoir plus <ArrowRight className="h-4 w-4" /></Link></article>)}</div>
                </div>
            </section>

            <section className="bg-[#f5f8fc] py-20">
                <div className="mx-auto max-w-7xl px-6">
                    <div className="flex flex-col justify-between gap-5 md:flex-row md:items-end"><div><p className="text-xs font-bold uppercase tracking-[0.2em] text-[#76c206]">Notre sélection</p><h2 className="mt-3 text-3xl font-extrabold text-[#111f3d] sm:text-4xl">Des biens qui vous ressemblent</h2><p className="mt-3 max-w-xl text-slate-500">Découvrez nos dernières opportunités disponibles à la location et à la vente.</p></div><Link href="/biens" className="inline-flex items-center gap-2 text-sm font-bold text-[#00559b]">Voir tous les biens <ArrowRight className="h-4 w-4" /></Link></div>
                    <div className="mt-10"><PropertyCards properties={properties} /></div>
                </div>
            </section>

            <section className="overflow-hidden bg-[#111f3d] py-20 text-white">
                <div className="mx-auto max-w-7xl px-6">
                    <div className="grid gap-10 lg:grid-cols-2 lg:items-center"><div><p className="text-xs font-bold uppercase tracking-[0.2em] text-[#76c206]">Pourquoi nous choisir</p><h2 className="mt-4 text-3xl font-extrabold sm:text-4xl">Un accompagnement pensé pour votre tranquillité.</h2><p className="mt-5 max-w-xl leading-7 text-white/60">De la première visite au suivi quotidien, nous protégeons vos intérêts avec transparence et réactivité.</p></div><div className="grid gap-4 sm:grid-cols-2">{[[ShieldCheck,'Transactions sécurisées'],[UsersRound,'Conseillers dédiés'],[WalletCards,'Suivi financier clair'],[Headphones,'Support réactif']].map(([Icon,label]) => <div key={label} className="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5"><span className="rounded-xl bg-[#76c206] p-3"><Icon className="h-5 w-5" /></span><span className="font-semibold">{label}</span></div>)}</div></div>
                    <div className="mt-14 grid grid-cols-2 gap-4 border-t border-white/10 pt-10 md:grid-cols-4">{[['500+','Biens gérés'],['350+','Locataires'],['120+','Propriétaires'],['4.9/5','Note moyenne']].map(([value,label]) => <div key={label}><p className="text-3xl font-extrabold text-[#76c206]">{value}</p><p className="mt-1 text-sm text-white/55">{label}</p></div>)}</div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-6 py-20">
                <div className="text-center"><p className="text-xs font-bold uppercase tracking-[0.2em] text-[#76c206]">Ils nous font confiance</p><h2 className="mt-3 text-3xl font-extrabold text-[#111f3d]">L’expérience de nos clients</h2></div>
                <div className="mt-10 grid gap-6 md:grid-cols-3">{[['Awa K.','Propriétaire','Une équipe disponible et des reversements toujours clairs. Je suis mon patrimoine sans stress.'],['Jean-Marc D.','Investisseur','Le suivi est précis et les tableaux de bord me permettent de décider rapidement.'],['Fatou B.','Locataire','Une recherche simple, une visite rapide et un accompagnement professionnel jusqu’à la remise des clés.']].map(([name,role,quote]) => <article key={name} className="rounded-3xl border border-slate-100 p-7 shadow-[0_14px_45px_rgba(17,31,61,0.07)]"><div className="flex gap-1 text-[#f6b51b]">{Array.from({ length: 5 }).map((_,i)=><Star key={i} className="h-4 w-4 fill-current" />)}</div><p className="mt-5 leading-7 text-slate-600">“{quote}”</p><div className="mt-6 flex items-center gap-3"><span className="flex h-11 w-11 items-center justify-center rounded-full bg-[#eaf4fb] font-bold text-[#00559b]">{name[0]}</span><div><p className="font-bold text-[#111f3d]">{name}</p><p className="text-xs text-slate-400">{role}</p></div></div></article>)}</div>
            </section>

            <section className="mx-auto max-w-7xl px-6 pb-20"><div className="relative overflow-hidden rounded-[2rem] bg-[#00559b] px-8 py-14 text-white sm:px-14"><div className="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#76c206]/30" /><div className="relative flex flex-col justify-between gap-8 lg:flex-row lg:items-center"><div><p className="text-sm font-bold text-[#9fdd42]">Vous avez un projet immobilier ?</p><h2 className="mt-2 max-w-2xl text-3xl font-extrabold">Parlons-en et construisons la solution qui vous convient.</h2></div><Link href="/contact" className="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-[#76c206] px-7 py-4 text-sm font-bold shadow-xl">Prendre rendez-vous <ArrowRight className="h-4 w-4" /></Link></div></div></section>
        </PublicLayout>
    );
}
