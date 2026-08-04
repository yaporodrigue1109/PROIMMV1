import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    BarChart3,
    Building2,
    Check,
    Clock3,
    FileText,
    Headphones,
    KeyRound,
    ShieldCheck,
    Smartphone,
    UsersRound,
    WalletCards,
} from 'lucide-react';
import PublicLayout from './PublicLayout';

const benefits = [
    {
        icon: Building2,
        title: 'Tout votre patrimoine au même endroit',
        text: 'Centralisez propriétés, bâtiments, portes, propriétaires et locataires dans un espace unique et structuré.',
    },
    {
        icon: WalletCards,
        title: 'Des loyers mieux suivis',
        text: 'Visualisez les paiements, impayés, arriérés et reversements sans multiplier les cahiers et fichiers Excel.',
    },
    {
        icon: BarChart3,
        title: 'Des décisions basées sur vos chiffres',
        text: 'Pilotez l’agence grâce à des statistiques claires sur l’occupation, les encaissements et la performance.',
    },
    {
        icon: UsersRound,
        title: 'Une équipe mieux organisée',
        text: 'Attribuez les accès à votre personnel et gardez une vision commune des opérations quotidiennes.',
    },
    {
        icon: ShieldCheck,
        title: 'Des données fiables et sécurisées',
        text: 'Conservez contrats, documents et historiques dans un environnement protégé et accessible.',
    },
    {
        icon: Headphones,
        title: 'Un accompagnement disponible',
        text: 'Créez et suivez vos demandes depuis le support intégré, avec une équipe prête à vous assister.',
    },
];

const modules = [
    'Gestion des propriétés et des lots',
    'Dossiers propriétaires et locataires',
    'Encaissement et suivi des loyers',
    'Reversements aux propriétaires',
    'Maintenance et interventions',
    'Gestion du personnel et des accès',
    'Statistiques et tableau de bord',
    'Support et suivi des demandes',
];

export default function About() {
    return (
        <PublicLayout>
            <Head title="Pourquoi Pros Immobilier" />

            <section className="relative overflow-hidden bg-[#111f3d] px-6 py-24 text-white lg:py-32">
                <div className="absolute -right-28 -top-36 h-96 w-96 rounded-full bg-[#00559b]/45 blur-3xl" />
                <div className="absolute -bottom-32 left-1/4 h-72 w-72 rounded-full bg-[#76c206]/20 blur-3xl" />
                <div className="relative mx-auto grid max-w-7xl gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div>
                        <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em]">
                            <BadgeCheck className="h-4 w-4 text-[#76c206]" /> Pensé pour les agences immobilières
                        </span>
                        <h1 className="mt-6 max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                            Moins de tâches manuelles. <span className="text-[#76c206]">Plus de contrôle.</span>
                        </h1>
                        <p className="mt-6 max-w-2xl text-lg leading-8 text-white/70">
                            Pros Immobilier aide votre agence à gagner du temps, sécuriser ses opérations et offrir un meilleur service aux propriétaires comme aux locataires.
                        </p>
                        <div className="mt-9 flex flex-wrap gap-4">
                            <Link href="/tarifs" className="inline-flex items-center gap-2 rounded-full bg-[#76c206] px-7 py-4 text-sm font-bold shadow-xl shadow-black/15 transition hover:-translate-y-0.5 hover:bg-[#66aa04]">
                                Découvrir nos offres <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link href="/contact" className="rounded-full border border-white/30 bg-white/10 px-7 py-4 text-sm font-bold backdrop-blur transition hover:bg-white hover:text-[#111f3d]">
                                Demander une démonstration
                            </Link>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        {[
                            ['Jusqu’à 60%', 'de temps gagné sur le suivi'],
                            ['100%', 'des opérations centralisées'],
                            ['24h/24', 'accès à vos informations'],
                            ['1 espace', 'pour toute votre agence'],
                        ].map(([value, label], index) => (
                            <div key={label} className={`rounded-3xl border p-6 backdrop-blur ${index === 0 ? 'border-[#76c206]/50 bg-[#76c206]/15' : 'border-white/10 bg-white/5'}`}>
                                <p className="text-2xl font-extrabold text-[#76c206] sm:text-3xl">{value}</p>
                                <p className="mt-2 text-sm leading-6 text-white/60">{label}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-6 py-20 lg:py-28">
                <div className="mx-auto max-w-3xl text-center">
                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-[#76c206]">Pourquoi vous inscrire ?</p>
                    <h2 className="mt-4 text-3xl font-extrabold text-[#111f3d] sm:text-4xl">Une application qui simplifie réellement le quotidien de votre agence</h2>
                    <p className="mt-5 leading-7 text-slate-500">Chaque fonctionnalité répond à un besoin concret de gestion, de suivi ou de communication.</p>
                </div>

                <div className="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {benefits.map(({ icon: Icon, title, text }) => (
                        <article key={title} className="group rounded-3xl border border-slate-100 bg-white p-7 shadow-[0_14px_45px_rgba(17,31,61,0.07)] transition hover:-translate-y-1 hover:border-[#76c206]/40">
                            <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#eaf4fb] text-[#00559b] transition group-hover:bg-[#76c206] group-hover:text-white"><Icon className="h-7 w-7" /></span>
                            <h3 className="mt-5 text-xl font-bold text-[#111f3d]">{title}</h3>
                            <p className="mt-3 leading-7 text-slate-500">{text}</p>
                        </article>
                    ))}
                </div>
            </section>

            <section className="bg-[#f5f8fc] py-20 lg:py-28">
                <div className="mx-auto grid max-w-7xl gap-12 px-6 lg:grid-cols-2 lg:items-center">
                    <div className="rounded-[2rem] bg-[#00559b] p-8 text-white shadow-2xl shadow-[#00559b]/15 sm:p-10">
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-[#b9ef68]">Une journée avec Pros Immobilier</p>
                        <div className="mt-8 space-y-6">
                            {[
                                [Clock3, 'Le matin', 'Consultez les loyers reçus, les impayés et les tâches prioritaires depuis votre tableau de bord.'],
                                [KeyRound, 'Dans la journée', 'Enregistrez un locataire, affectez une porte ou planifiez une intervention en quelques étapes.'],
                                [FileText, 'En fin de période', 'Préparez les reversements propriétaires et retrouvez chaque détail sans recalcul manuel.'],
                            ].map(([Icon, title, text]) => (
                                <div key={title} className="flex gap-4 rounded-2xl border border-white/10 bg-white/5 p-5">
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#76c206]"><Icon className="h-5 w-5" /></span>
                                    <div><h3 className="font-bold">{title}</h3><p className="mt-1 text-sm leading-6 text-white/65">{text}</p></div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-[#76c206]">Tout ce dont votre agence a besoin</p>
                        <h2 className="mt-4 text-3xl font-extrabold leading-tight text-[#111f3d] sm:text-4xl">Remplacez les outils dispersés par une seule plateforme.</h2>
                        <p className="mt-5 leading-7 text-slate-500">Vos collaborateurs travaillent avec les mêmes informations, vos historiques restent accessibles et vos clients bénéficient d’un suivi plus professionnel.</p>
                        <div className="mt-8 grid gap-3 sm:grid-cols-2">
                            {modules.map((module) => (
                                <div key={module} className="flex items-start gap-3 rounded-xl bg-white p-3 shadow-sm">
                                    <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#76c206] text-white"><Check className="h-3 w-3" /></span>
                                    <span className="text-sm font-medium text-[#334155]">{module}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-6 py-20 lg:py-28">
                <div className="grid gap-6 md:grid-cols-3">
                    {[
                        [Smartphone, 'Accessible partout', 'Au bureau, sur le terrain ou en déplacement, consultez les informations utiles depuis votre navigateur.'],
                        [ShieldCheck, 'Traçabilité renforcée', 'Conservez les paiements, contrats, interventions et échanges pour réduire les erreurs et les litiges.'],
                        [BarChart3, 'Croissance maîtrisée', 'Gérez davantage de biens et de clients sans perdre en qualité de service ni en visibilité.'],
                    ].map(([Icon, title, text]) => (
                        <div key={title} className="text-center">
                            <span className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eaf4fb] text-[#00559b]"><Icon className="h-8 w-8" /></span>
                            <h3 className="mt-5 text-xl font-bold text-[#111f3d]">{title}</h3>
                            <p className="mt-3 leading-7 text-slate-500">{text}</p>
                        </div>
                    ))}
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-6 pb-20">
                <div className="relative overflow-hidden rounded-[2rem] bg-[#111f3d] px-8 py-14 text-white sm:px-14">
                    <div className="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#76c206]/25" />
                    <div className="relative flex flex-col justify-between gap-8 lg:flex-row lg:items-center">
                        <div>
                            <p className="text-sm font-bold text-[#9fdd42]">Prêt à moderniser votre agence ?</p>
                            <h2 className="mt-2 max-w-2xl text-3xl font-extrabold">Inscrivez votre agence et commencez à gérer plus efficacement.</h2>
                            <p className="mt-3 text-sm text-white/60">Notre équipe vous accompagne dans la prise en main de la plateforme.</p>
                        </div>
                        <div className="flex shrink-0 flex-wrap gap-3">
                            <Link href="/contact" className="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold hover:bg-white hover:text-[#111f3d]">Demander une démo</Link>
                            <Link href="/tarifs" className="inline-flex items-center gap-2 rounded-full bg-[#76c206] px-6 py-3.5 text-sm font-bold">Voir les offres <ArrowRight className="h-4 w-4" /></Link>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
