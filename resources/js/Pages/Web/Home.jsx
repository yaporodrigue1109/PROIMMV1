import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, ArrowUpRight, BarChart3, Building2, CalendarClock, Check, DoorOpen, FileCheck2, Headphones, Home as HomeIcon, Inbox, KeyRound, LayoutDashboard, Plus, Search, ShieldCheck, Star, UsersRound, WalletCards, Wrench } from 'lucide-react';
import { useEffect, useState } from 'react';
import ownerMobileApp from '../../assets/app-mobile-proprietaire.png';
import tenantMobileApp from '../../assets/app-mobile-locataire.png';
import PublicLayout from './PublicLayout';
import defaultLogo from '../../../../admin/logo/playstore-icon-revised.png';

const services = [
    { icon: KeyRound, title: 'Gestion locative', text: 'Loyers, contrats et reversements suivis automatiquement, sans tableur ni papier.' },
    { icon: HomeIcon, title: 'Suivi des biens et lots', text: 'Chaque propriété, chaque lot et chaque propriétaire centralisés au même endroit.' },
    { icon: BarChart3, title: 'Pilotage de l’agence', text: 'Impayés, encaissements et occupation visibles en un coup d’œil, à jour en temps réel.' },
];

const advantages = [
    { icon: ShieldCheck, label: 'Données sécurisées', text: 'Vos contrats, paiements et documents sont protégés et sauvegardés.' },
    { icon: UsersRound, label: 'Prise en main rapide', text: 'Votre équipe est opérationnelle en quelques jours, sans formation lourde.' },
    { icon: WalletCards, label: 'Reversements automatisés', text: 'Fini les calculs manuels : vos propriétaires sont payés au bon montant, au bon moment.' },
    { icon: Headphones, label: 'Support réactif', text: 'Une équipe joignable pour répondre à vos questions, sans délai d’attente interminable.' },
];

const testimonials = [
    { name: 'Awa K.', role: 'Gérante d’agence', quote: 'Je ne calcule plus les reversements à la main. Ce que je faisais en deux jours prend maintenant deux heures.' },
    { name: 'Jean-Marc D.', role: 'Directeur d’agence', quote: 'J’ai enfin une vue claire sur mes impayés. Je sais qui me doit quoi sans éplucher mes cahiers.' },
    { name: 'Fatou B.', role: 'Responsable locative', quote: 'Toute l’équipe travaille sur les mêmes données. Plus de dossiers qui se contredisent entre collègues.' },
];

const heroTabs = [
    { icon: LayoutDashboard, label: 'Tableau de bord' },
    { icon: Building2, label: 'Propriétés' },
    { icon: UsersRound, label: 'Locataires' },
    { icon: WalletCards, label: 'Caisse' },
    { icon: Wrench, label: 'Maintenance' },
];

const formatMoney = (value) => new Intl.NumberFormat('fr-FR', {
    maximumFractionDigits: 0,
}).format(Number(value ?? 0));

const moduleDescriptions = {
    'SMS (Illimité)': 'Envoyez sans limite les rappels de paiement, confirmations, quittances et informations importantes à vos clients.',
    'WhatsApp Business': 'Communiquez avec vos propriétaires et locataires sur WhatsApp et partagez rapidement avis, reçus et notifications.',
    'Portail web': 'Présentez votre agence et publiez vos biens disponibles sur un espace web professionnel accessible à vos prospects.',
    'Statistiques avancées': 'Analysez les loyers, impayés, dépenses, taux d’occupation et performances de votre portefeuille avec des rapports détaillés.',
    'Portail propriétaire': 'Offrez à chaque propriétaire un accès sécurisé à ses biens, encaissements, dépenses et états de reversement.',
    'Portail locataire': 'Permettez aux locataires de consulter leurs échéances, télécharger leurs quittances et suivre leurs demandes de maintenance.',
};

const includedSubscriptionFeatures = [
    'Gestion des biens, bâtiments et lots',
    'Gestion des propriétaires et locataires',
    'Suivi des loyers, paiements et impayés',
    'Reversements aux propriétaires',
    'Gestion des maintenances',
    'Tableau de bord de l’agence',
];

const dashboardRows = [
    ['AK', 'Awa Koné', 'Résidence Riviera', '450 000 F'],
    ['JM', 'Jean-Marc Diarra', 'Villa Cocody', '725 000 F'],
    ['FB', 'Fatou Bamba', 'Studio Marcory', '280 000 F'],
];

export default function Home({ tarifs = {}, appLinks = {} }) {
    const config = usePage().props.siteConfig ?? {};
    const logo = config.logoUrl || defaultLogo;
    const companyName = config.name || 'Pros Immobilier';
    const [activeHeroTab, setActiveHeroTab] = useState(0);
    const plan = tarifs.plan ?? {};
    const complementaryModules = Array.isArray(tarifs.modules) ? tarifs.modules : [];

    useEffect(() => {
        const interval = window.setInterval(() => {
            setActiveHeroTab((current) => (current + 1) % heroTabs.length);
        }, 5000);

        return () => window.clearInterval(interval);
    }, []);

    return (
        <PublicLayout>
            <Head title="Accueil" />

            {/* HERO */}
            <section className="overflow-hidden bg-white">
                <div className="mx-auto max-w-7xl px-6 pb-16 pt-8 sm:pb-20 sm:pt-10 lg:pb-24 lg:pt-12">
                    <div className="grid items-end gap-10 lg:grid-cols-[1fr_auto]">
                        <div className="max-w-4xl">
                            <h1 className="font-sans text-[clamp(2.65rem,6vw,5.25rem)] font-medium leading-[0.98] tracking-[-0.055em] text-[#0b0b0d] text-balance">
                                Le logiciel qui facilite la gestion de votre agence immobilière.
                            </h1>
                            <p className="mt-7 max-w-2xl text-base leading-7 text-slate-500 sm:text-lg">
                                Fini les tableurs, les carnets de reçus et les calculs de reversement à la main. Pros Immobilier centralise vos biens, vos locataires et votre trésorerie dans un seul outil.
                            </p>
                        </div>

                        <div className="flex flex-wrap items-center gap-6 pb-1 lg:justify-end">
                            <Link href="/inscription-agence" className="inline-flex items-center gap-2 rounded-[8px] bg-[#76c206] px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-[#66aa04]">
                                Créer mon agence <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link href="/tarifs" className="inline-flex items-center gap-2 text-sm font-semibold text-[#00559b] transition-colors hover:text-[#004a87]">
                                Voir les tarifs <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="border-b border-slate-200">
                    <div className="mx-auto grid max-w-7xl grid-cols-2 px-6 sm:grid-cols-3 lg:grid-cols-5">
                        {heroTabs.map(({ icon: Icon, label }, index) => (
                            <button
                                key={label}
                                type="button"
                                onClick={() => setActiveHeroTab(index)}
                                className={`relative flex items-center justify-center gap-2.5 border-slate-100 px-3 py-5 text-xs font-medium transition-colors sm:text-sm lg:border-r ${activeHeroTab === index ? 'text-[#111f3d]' : 'text-slate-400 hover:text-[#00559b]'}`}
                                aria-pressed={activeHeroTab === index}
                            >
                                <Icon className="h-4 w-4" />
                                <span>{label}</span>
                                {activeHeroTab === index ? <span className="absolute inset-x-0 bottom-0 h-0.5 bg-[#00559b]" /> : null}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="relative min-h-[390px] bg-[#e7f4fb] px-4 pb-12 pt-12 sm:min-h-[520px] sm:px-8 sm:pb-16 sm:pt-16 lg:min-h-[640px] lg:pb-20">
                    <div className="absolute inset-0 opacity-90 [background-image:radial-gradient(circle_at_15%_18%,rgba(118,194,6,.7),transparent_24%),radial-gradient(circle_at_33%_4%,rgba(48,188,237,.8),transparent_29%),radial-gradient(circle_at_57%_14%,rgba(255,175,67,.75),transparent_24%),radial-gradient(circle_at_82%_12%,rgba(241,93,181,.72),transparent_28%),radial-gradient(circle_at_63%_83%,rgba(254,220,90,.65),transparent_28%),radial-gradient(circle_at_17%_80%,rgba(60,190,225,.7),transparent_30%)]" />
                    <div className="absolute inset-0 opacity-40 [background-image:url('data:image/svg+xml,%3Csvg_viewBox=%220_0_180_180%22_xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter_id=%22n%22%3E%3CfeTurbulence_type=%22fractalNoise%22_baseFrequency=%22.9%22_numOctaves=%223%22_stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect_width=%22100%25%22_height=%22100%25%22_filter=%22url(%23n)%22_opacity=%22.35%22/%3E%3C/svg%3E')]" />

                    <div className="relative mx-auto max-w-6xl rounded-xl border border-white/70 bg-white/90 p-2 shadow-[0_25px_80px_rgba(15,23,42,0.28)] backdrop-blur-sm sm:p-3">
                        <DashboardPreview activeModule={activeHeroTab} logo={logo} companyName={companyName} />
                    </div>
                </div>
            </section>

            {/* ABOUT + SERVICES */}
            <section className="mx-auto max-w-7xl px-6 py-20 lg:py-28">
                <div className="grid gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
                    <div>
                        <h2 className="font-sans text-3xl font-medium leading-tight text-[#111f3d] sm:text-4xl text-balance">
                            Un outil pensé pour le quotidien des agences.
                        </h2>
                        <p className="mt-6 leading-relaxed text-slate-600">
                            Pros Immobilier a été conçu avec des agences de gestion locative pour remplacer les fichiers Excel, les groupes WhatsApp et les cahiers de comptes par un seul outil fiable.
                        </p>
                        <div className="mt-10 flex divide-x divide-slate-200 border-y border-slate-200">
                            <div className="pr-8 py-5">
                                <p className="font-sans text-4xl text-[#00559b]">500+</p>
                                <p className="mt-1 text-sm text-slate-500">Biens gérés sur la plateforme</p>
                            </div>
                            <div className="px-8 py-5">
                                <p className="font-sans text-4xl text-[#00559b]">4,9/5</p>
                                <p className="mt-1 text-sm text-slate-500">Note moyenne des agences clientes</p>
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

            {/* PRICING */}
            <section className="bg-white pb-20 lg:pb-28">
                <div className="mx-auto max-w-7xl px-6">
                    <div className="overflow-hidden border border-slate-200 lg:grid lg:grid-cols-[0.85fr_1.15fr]">
                        <div className="flex flex-col justify-between bg-[#0c1a35] p-8 text-white sm:p-10 lg:p-12">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#9fdd42]">Tarification simple</p>
                                <h2 className="mt-4 font-sans text-3xl font-medium leading-tight sm:text-4xl text-balance">
                                    Une formule complète pour démarrer dès aujourd’hui.
                                </h2>
                                <p className="mt-5 leading-relaxed text-white/60">
                                    Un abonnement principal qui rassemble les fonctions indispensables à la gestion quotidienne de votre agence.
                                </p>
                            </div>
                            <Link href="/tarifs" className="mt-10 inline-flex w-fit items-center gap-2 text-sm font-semibold text-[#9fdd42] transition-colors hover:text-white">
                                Consulter tous les tarifs <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        </div>

                        <div className="p-8 sm:p-10 lg:p-12">
                            <div className="flex flex-col justify-between gap-6 border-b border-slate-200 pb-8 sm:flex-row sm:items-start">
                                <div>
                                    <p className="text-sm font-semibold text-[#00559b]">Abonnement principal</p>
                                    <h3 className="mt-2 text-2xl font-semibold text-[#111f3d]">{plan.nom || 'Abonnement de base'}</h3>
                                    {plan.description ? <div className="mt-2 max-w-lg text-sm leading-6 text-slate-500" dangerouslySetInnerHTML={{ __html: plan.description }} /> : null}
                                </div>
                                <div className="shrink-0 sm:text-right">
                                    <p className="font-sans text-4xl font-medium tracking-tight text-[#00559b]">{formatMoney(plan.prix_mensuel)} FCFA</p>
                                    <p className="mt-1 text-sm text-slate-400">par mois</p>
                                </div>
                            </div>

                            <div className="grid gap-x-8 gap-y-4 py-8 sm:grid-cols-2">
                                {includedSubscriptionFeatures.map((feature) => (
                                    <div key={feature} className="flex items-start gap-3 text-sm text-slate-600">
                                        <span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[#76c206]" />
                                        <span>{feature}</span>
                                    </div>
                                ))}
                            </div>

                            <div className="flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">
                                <p className="max-w-md text-sm leading-6 text-slate-500">Ajoutez ensuite les modules complémentaires qui correspondent aux besoins de votre équipe.</p>
                                <Link href="/inscription-agence" className="inline-flex shrink-0 items-center justify-center gap-2 rounded-[8px] bg-[#76c206] px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-[#66aa04]">
                                    Commencer <ArrowRight className="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* COMPLEMENTARY MODULES */}
            {complementaryModules.length > 0 ? (
                <section className="border-y border-slate-200 bg-[#f6f9fc] py-20 lg:py-28">
                    <div className="mx-auto max-w-7xl px-6">
                        <div className="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                            <div className="max-w-2xl">
                                <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#76c206]">Modules complémentaires</p>
                                <h2 className="mt-3 font-sans text-3xl font-medium leading-tight text-[#111f3d] sm:text-4xl text-balance">
                                    Un abonnement qui s’adapte à votre agence.
                                </h2>
                                <p className="mt-5 max-w-xl leading-relaxed text-slate-500">
                                    Ajoutez uniquement les outils dont votre équipe a besoin. Chaque module vient compléter votre abonnement principal, sans rien imposer.
                                </p>
                            </div>
                            <Link href="/tarifs" className="inline-flex w-fit items-center gap-2 text-sm font-semibold text-[#00559b] transition-colors hover:text-[#004a87]">
                                Voir l’offre complète <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        </div>

                        <div className="mt-12 grid gap-px overflow-hidden border border-slate-200 bg-slate-200 md:grid-cols-2 lg:grid-cols-3">
                            {complementaryModules.map((module) => (
                                <article key={module.id ?? module.label} className="flex min-h-60 flex-col bg-white p-7 transition-colors hover:bg-[#fafdff] sm:p-8">
                                    <h3 className="text-lg font-semibold text-[#111f3d]">{module.label}</h3>
                                    <p className="mt-3 flex-1 text-sm leading-6 text-slate-500">
                                        {module.description || moduleDescriptions[module.label] || 'Étendez les possibilités de votre espace avec une fonctionnalité adaptée à l’activité de votre agence.'}
                                    </p>
                                    <p className="mt-6 border-t border-slate-100 pt-5 text-sm text-slate-400">
                                        {Number(module.prix_mensuel) > 0 ? (
                                            <><span className="text-xl font-semibold text-[#00559b]">+{formatMoney(module.prix_mensuel)} FCFA</span> / mois</>
                                        ) : (
                                            <span className="font-semibold text-[#4d8500]">Inclus sans supplément</span>
                                        )}
                                    </p>
                                </article>
                            ))}
                        </div>

                        <div className="mt-8 flex flex-col justify-between gap-5 border-l-4 border-[#76c206] bg-[#0c1a35] px-6 py-6 text-white sm:flex-row sm:items-center sm:px-8">
                            <div>
                                <p className="font-semibold">Composez une formule adaptée à votre façon de travailler.</p>
                                <p className="mt-1 text-sm text-white/60">Vous ne payez que pour les modules complémentaires que vous choisissez.</p>
                            </div>
                            <Link href="/inscription-agence" className="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-[#9fdd42] hover:text-white">
                                Créer mon agence <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </section>
            ) : null}

            {/* OWNER MOBILE APP */}
            <section className="border-b border-slate-200 bg-white py-20 lg:py-28">
                <div className="mx-auto grid max-w-7xl items-center gap-12 px-6 lg:grid-cols-2 lg:gap-20">
                    <div className="relative overflow-hidden rounded-[28px] bg-[#eaf4fb]">
                        <div className="absolute -left-16 top-12 h-48 w-48 rounded-full bg-[#76c206]/20 blur-3xl" />
                        <div className="absolute -right-16 bottom-10 h-56 w-56 rounded-full bg-[#00559b]/20 blur-3xl" />
                        <img
                            src={ownerMobileApp}
                            alt="Application mobile Pros Immobilier pour propriétaire"
                            className="relative aspect-[3/2] h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>

                    <div className="max-w-xl">
                        <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#76c206]">Application propriétaire</p>
                        <h2 className="mt-4 font-sans text-3xl font-medium leading-tight text-[#111f3d] sm:text-4xl text-balance">
                            Vos biens et vos revenus toujours à portée de main.
                        </h2>
                        <p className="mt-6 leading-relaxed text-slate-500">
                            Depuis son téléphone, chaque propriétaire garde une vue claire sur son patrimoine et suit les opérations réalisées par l’agence en toute transparence.
                        </p>
                        <ul className="mt-8 space-y-4">
                            {[
                                'Suivi des loyers encaissés et des reversements',
                                'Vue détaillée des biens occupés et disponibles',
                                'Consultation des dépenses et documents importants',
                            ].map((feature) => (
                                <li key={feature} className="flex items-start gap-3 text-sm leading-6 text-slate-600">
                                    <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#eef8df] text-[#4d8500]">
                                        <Check className="h-3.5 w-3.5" strokeWidth={2.5} />
                                    </span>
                                    {feature}
                                </li>
                            ))}
                        </ul>
                        <StoreDownloadButtons links={appLinks.owner} />

                    </div>
                </div>
            </section>

            {/* TENANT MOBILE APP */}
            <section className="border-b border-slate-200 bg-[#f6f9fc] py-20 lg:py-28">
                <div className="mx-auto grid max-w-7xl items-center gap-12 px-6 lg:grid-cols-2 lg:gap-20">
                    <div className="max-w-xl">
                        <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#76c206]">Application locataire</p>
                        <h2 className="mt-4 font-sans text-3xl font-medium leading-tight text-[#111f3d] sm:text-4xl text-balance">
                            Le quotidien du locataire devient plus simple.
                        </h2>
                        <p className="mt-6 leading-relaxed text-slate-500">
                            Le locataire retrouve ses échéances, ses quittances et ses échanges avec l’agence dans une application intuitive, accessible à tout moment.
                        </p>
                        <ul className="mt-8 space-y-4">
                            {[
                                'Consultation des loyers à venir et de leur statut',
                                'Téléchargement rapide des quittances',
                                'Création et suivi des demandes de maintenance',
                            ].map((feature) => (
                                <li key={feature} className="flex items-start gap-3 text-sm leading-6 text-slate-600">
                                    <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#eef8df] text-[#4d8500]">
                                        <Check className="h-3.5 w-3.5" strokeWidth={2.5} />
                                    </span>
                                    {feature}
                                </li>
                            ))}
                        </ul>
                        <StoreDownloadButtons links={appLinks.tenant} />

                    </div>

                    <div className="relative overflow-hidden rounded-[28px] bg-[#dcecf7]">
                        <div className="absolute -left-16 bottom-8 h-52 w-52 rounded-full bg-[#76c206]/20 blur-3xl" />
                        <div className="absolute -right-14 top-10 h-52 w-52 rounded-full bg-[#00559b]/20 blur-3xl" />
                        <img
                            src={tenantMobileApp}
                            alt="Application mobile Pros Immobilier pour locataire"
                            className="relative aspect-[3/2] h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                </div>
            </section>

            {/* WHY US */}
            <section className="bg-[#0c1a35] py-20 text-white lg:py-28">
                <div className="mx-auto max-w-7xl px-6">
                    <div className="max-w-2xl">
                        <h2 className="font-sans text-3xl font-medium leading-tight sm:text-4xl text-balance">
                            Un outil pensé pour vous faire gagner du temps.
                        </h2>
                        <p className="mt-5 leading-relaxed text-white/60">
                            De l’inscription d’un bien au reversement au propriétaire, chaque étape est suivie automatiquement pour que rien ne vous échappe.
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
                        {[['500+', 'Biens gérés'], ['350+', 'Locataires suivis'], ['120+', 'Propriétaires accompagnés'], ['4.9/5', 'Note moyenne des agences']].map(([value, label]) => (
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
                <h2 className="max-w-md font-sans text-3xl font-medium text-[#111f3d] sm:text-4xl">Ce qu’en disent les agences qui l’utilisent</h2>
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
                        <p className="text-sm font-semibold text-[#9fdd42]">Prêt à simplifier la gestion de votre agence ?</p>
                        <h2 className="mt-2 max-w-2xl font-sans text-3xl font-medium leading-tight text-balance">
                            Parlons de vos besoins et voyons comment Pros Immobilier peut vous aider.
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

function StoreDownloadButtons({ links = {} }) {
    const stores = [
        {
            label: 'Google Play',
            eyebrow: 'Disponible sur',
            href: links.android || 'https://play.google.com/store/search?q=Pros%20Immobilier&c=apps',
            icon: GooglePlayLogo,
        },
        {
            label: 'App Store',
            eyebrow: 'Télécharger dans',
            href: links.ios || 'https://apps.apple.com/fr/search?term=Pros%20Immobilier',
            icon: AppleLogo,
        },
    ];

    return (
        <div className="mt-9 flex flex-wrap gap-3">
            {stores.map(({ label, eyebrow, href, icon: Icon }) => (
                <a
                    key={label}
                    href={href}
                    target="_blank"
                    rel="noreferrer"
                    aria-label={`${eyebrow} ${label}`}
                    className="inline-flex min-w-40 items-center gap-3 rounded-xl bg-[#0c1a35] px-4 py-2.5 text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#00559b] hover:shadow-md"
                >
                    <Icon className="h-8 w-8 shrink-0" />
                    <span className="text-left leading-none">
                        <span className="block text-[10px] text-white/65">{eyebrow}</span>
                        <span className="mt-1 block text-base font-semibold tracking-tight">{label}</span>
                    </span>
                </a>
            ))}
        </div>
    );
}

function GooglePlayLogo({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#00d6ff" d="M3.4 2.6a2 2 0 0 0-.4 1.2v16.4c0 .45.14.87.4 1.2L13.1 12 3.4 2.6Z" />
            <path fill="#00ef77" d="m4.25 2.05 11.9 6.77L13.1 12 3.4 2.6c.25-.22.54-.39.85-.55Z" />
            <path fill="#ffdf00" d="m16.15 8.82 3.75 2.13c1.06.6 1.06 1.5 0 2.1l-3.77 2.14L13.1 12l3.05-3.18Z" />
            <path fill="#ff3a44" d="m16.13 15.19-11.9 6.77a2.4 2.4 0 0 1-.83-.56l9.7-9.4 3.03 3.19Z" />
        </svg>
    );
}

function AppleLogo({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M17.05 12.54c.03 3.19 2.8 4.25 2.83 4.27-.02.08-.44 1.52-1.46 3-.88 1.28-1.8 2.55-3.24 2.58-1.42.03-1.87-.84-3.49-.84-1.61 0-2.12.81-3.46.87-1.39.05-2.45-1.39-3.34-2.66-1.82-2.63-3.21-7.44-1.34-10.68a5.18 5.18 0 0 1 4.4-2.67c1.37-.03 2.67.93 3.49.93.82 0 2.35-1.15 3.96-.98.68.03 2.58.27 3.8 2.06-.1.06-2.27 1.32-2.25 4.12ZM14.34 4.66c.74-.9 1.24-2.15 1.1-3.4-1.07.04-2.37.71-3.14 1.61-.69.8-1.29 2.07-1.13 3.29 1.2.09 2.42-.61 3.17-1.5Z" />
        </svg>
    );
}

function DashboardPreview({ activeModule, logo, companyName }) {
    const stats = [
        { label: 'Propriétés', value: '48', hint: '41 occupées · 7 libres', icon: Building2, green: true },
        { label: 'Personnel', value: '12', hint: 'Membres de l’agence', icon: UsersRound },
        { label: 'Propriétaires', value: '32', hint: '64 lots gérés', icon: KeyRound, green: true },
        { label: 'Locataires actifs', value: '41', hint: 'Contrats en cours', icon: UsersRound },
    ];

    return (
        <div className="h-[360px] overflow-hidden rounded-lg border border-[#c8d4de] bg-[#f7fbfe] text-[#0f172a] sm:h-[500px] lg:h-[535px]">
            <div className="grid h-full grid-cols-1 sm:grid-cols-[175px_1fr] lg:grid-cols-[215px_1fr]">
                <aside className="hidden border-r border-[#c8d4de] bg-white sm:block">
                    <div className="flex h-14 items-center gap-2.5 border-b border-[#c8d4de] px-3 lg:px-4">
                        <img src={logo} alt={companyName} className="h-8 w-8 rounded-xl object-contain shadow-sm ring-1 ring-[#c8d4de]" />
                        <div>
                            <p className="text-[10px] font-semibold">{companyName}</p>
                            <p className="text-[8px] text-[#5f7182]">Espace agence</p>
                        </div>
                    </div>
                    <nav className="space-y-1 p-3">
                        {[
                            [HomeIcon, 'Tableau de bord'], [Building2, 'Propriétés'], [KeyRound, 'Propriétaires'],
                            [UsersRound, 'Locataires'], [UsersRound, 'Personnel'], [Wrench, 'Maintenance'], [WalletCards, 'Caisse'],
                            [BarChart3, 'Statistiques'], [Inbox, 'Support'],
                        ].map(([Icon, label]) => (
                            <div key={label} className={`flex items-center gap-2 rounded-lg px-2.5 py-2 text-[9px] ${label === heroTabs[activeModule].label ? 'bg-[#00559b] font-semibold text-white' : 'text-[#5f7182]'}`}>
                                <Icon className="h-3.5 w-3.5" /> {label}
                            </div>
                        ))}
                    </nav>
                </aside>

                <div className="min-w-0 overflow-hidden">
                    <header className="flex h-14 items-center justify-between border-b border-[#c8d4de] bg-white px-3 sm:px-4">
                        <p className="text-[11px] font-semibold sm:text-xs">{heroTabs[activeModule].label}</p>
                        <div className="flex items-center gap-2 rounded-lg border border-[#c8d4de] px-2 py-1.5">
                            <span className="flex h-6 w-6 items-center justify-center rounded-full bg-[#00559b] text-[8px] font-semibold text-white">AG</span>
                            <span className="hidden text-[9px] font-medium lg:inline">Agence Horizon</span>
                        </div>
                    </header>

                    {activeModule === 0 ? <div className="p-3 sm:p-4 lg:p-5">
                        <div className="flex items-end justify-between gap-3">
                            <div>
                                <p className="text-[8px] text-[#5f7182] sm:text-[9px]">Activité financière · Août 2026</p>
                                <h3 className="mt-0.5 text-sm font-semibold sm:text-base">Bonjour, voici votre activité</h3>
                            </div>
                            <button type="button" className="hidden items-center gap-1.5 rounded-lg bg-[#00559b] px-3 py-2 text-[9px] font-semibold text-white sm:flex">
                                <Plus className="h-3 w-3" /> Ajouter une propriété
                            </button>
                        </div>

                        <div className="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
                            {stats.map(({ label, value, hint, icon: Icon, green }) => (
                                <article key={label} className="rounded-xl border border-[#c8d4de] bg-white p-2.5 shadow-sm lg:p-3">
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <p className="text-[8px] font-medium text-[#5f7182] lg:text-[9px]">{label}</p>
                                            <p className="mt-1 text-base font-semibold lg:text-lg">{value}</p>
                                        </div>
                                        <span className={`flex h-7 w-7 shrink-0 items-center justify-center rounded-lg ${green ? 'bg-[#eef8df] text-[#4d8500]' : 'bg-[#eaf4fb] text-[#00559b]'}`}>
                                            <Icon className="h-3.5 w-3.5" />
                                        </span>
                                    </div>
                                    <p className="mt-1 truncate text-[7px] text-[#5f7182] lg:text-[8px]">{hint}</p>
                                </article>
                            ))}
                        </div>

                        <article className="mt-3 rounded-xl border border-[#c8d4de] bg-white p-3 shadow-sm">
                            <p className="text-[10px] font-semibold">Résumé par module</p>
                            <p className="text-[8px] text-[#5f7182]">Vue synthétique des activités de l’agence</p>
                            <div className="mt-2 grid grid-cols-3 gap-2 lg:grid-cols-5">
                                {[
                                    ['Immobilier', '48', '41 occupées'], ['Locataires', '41', 'Contrats actifs'],
                                    ['Propriétaires', '32', '64 lots gérés'], ['Personnel', '12', 'Membres actifs'],
                                    ['Finances', '9,8 M F', '8,4 M F versés'],
                                ].map(([label, value, hint], index) => (
                                    <div key={label} className={`${index > 2 ? 'hidden lg:block' : ''} rounded-lg border border-[#eef3f7] bg-[#f7fbfe] p-2`}>
                                        <p className="text-[6px] font-semibold uppercase tracking-wide text-[#5f7182]">{label}</p>
                                        <p className="mt-1 text-xs font-semibold">{value}</p>
                                        <p className="mt-0.5 text-[6px] text-[#5f7182]">{hint}</p>
                                    </div>
                                ))}
                            </div>
                        </article>

                        <div className="mt-3 grid gap-3 lg:grid-cols-[1.4fr_.6fr]">
                            <article className="overflow-hidden rounded-xl border border-[#c8d4de] bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-[#eef3f7] px-3 py-2">
                                    <div><p className="text-[10px] font-semibold">Paiements récents</p><p className="text-[7px] text-[#5f7182]">Derniers encaissements enregistrés</p></div>
                                    <span className="text-[8px] font-medium text-[#00559b]">Tout voir</span>
                                </div>
                                {dashboardRows.map(([initials, name, property, amount]) => (
                                    <div key={name} className="flex items-center justify-between gap-2 border-b border-[#eef3f7] px-3 py-2 last:border-0">
                                        <div className="flex min-w-0 items-center gap-2">
                                            <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#eaf4fb] text-[7px] font-semibold text-[#00559b]">{initials}</span>
                                            <div className="min-w-0"><p className="truncate text-[8px] font-medium">{name}</p><p className="truncate text-[6px] text-[#5f7182]">{property}</p></div>
                                        </div>
                                        <div className="flex items-center gap-1.5"><span className="text-[8px] font-semibold">{amount}</span><span className="rounded-full bg-[#eef8df] px-1.5 py-0.5 text-[6px] text-[#4d8500]">payé</span></div>
                                    </div>
                                ))}
                            </article>

                            <article className="hidden rounded-xl border border-[#c8d4de] bg-white p-3 shadow-sm lg:block">
                                <p className="flex items-center gap-1.5 text-[10px] font-semibold"><CalendarClock className="h-3.5 w-3.5 text-[#00559b]" /> Échéances à venir</p>
                                <p className="mt-0.5 text-[7px] text-[#5f7182]">Contrats à renouveler</p>
                                <div className="mt-3 rounded-lg border border-[#eef3f7] bg-[#f7fbfe] p-2">
                                    <p className="text-[8px] font-medium">Mariam Traoré</p><p className="text-[6px] text-[#5f7182]">Appartement Plateau</p>
                                    <div className="mt-2 flex justify-between border-t border-[#c8d4de] pt-1.5 text-[6px]"><span>Fin de bail</span><span className="font-medium text-[#00559b]">28/08/2026</span></div>
                                </div>
                            </article>
                        </div>
                    </div> : <ModulePreview activeModule={activeModule} />}
                </div>
            </div>
        </div>
    );
}

function ModulePreview({ activeModule }) {
    if (activeModule === 1) return <PropertiesPreview />;
    if (activeModule === 2) return <TenantsPreview />;
    if (activeModule === 3) return <CashPreview />;
    return <MaintenancePreview />;
}

function PreviewHeading({ title, action, secondary }) {
    return (
        <div className="flex items-end justify-between gap-3">
            <h3 className="text-sm font-semibold sm:text-base">{title}</h3>
            <div className="hidden items-center gap-1.5 sm:flex">
                {secondary ? <span className="rounded-lg border border-[#c8d4de] bg-white px-2.5 py-2 text-[8px] font-medium">{secondary}</span> : null}
                <span className="flex items-center gap-1 rounded-lg bg-[#00559b] px-3 py-2 text-[8px] font-semibold text-white"><Plus className="h-3 w-3" />{action}</span>
            </div>
        </div>
    );
}

function TopMetricCards({ items, sideIcon = false }) {
    return (
        <div className="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
            {items.map(({ label, value, icon: Icon, tone = 'blue', foot }) => (
                <article key={label} className={`rounded-xl border border-[#c8d4de] bg-white shadow-sm ${sideIcon ? 'flex items-center gap-2 p-2.5 lg:p-3' : 'p-2.5 lg:p-3'}`}>
                    {sideIcon ? <span className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${tone === 'green' ? 'bg-[#eef8df] text-[#4d8500]' : tone === 'red' ? 'bg-[#fdecec] text-[#b42318]' : 'bg-[#eaf4fb] text-[#00559b]'}`}><Icon className="h-4 w-4" /></span> : null}
                    <div className="min-w-0 flex-1">
                        {sideIcon ? <><p className="text-sm font-semibold lg:text-base">{value}</p><p className="truncate text-[6px] font-medium uppercase tracking-wide text-[#5f7182] lg:text-[7px]">{label}</p></> : <><div className="flex items-start justify-between gap-2"><p className="truncate text-[7px] font-medium text-[#5f7182] lg:text-[8px]">{label}</p><span className={`hidden h-7 w-7 shrink-0 items-center justify-center rounded-lg sm:flex ${tone === 'green' ? 'bg-[#eef8df] text-[#4d8500]' : tone === 'red' ? 'bg-[#fdecec] text-[#b42318]' : 'bg-[#eaf4fb] text-[#00559b]'}`}><Icon className="h-3.5 w-3.5" /></span></div><p className="mt-1 text-sm font-semibold lg:text-base">{value}</p></>}
                        {foot ? <p className="mt-0.5 truncate text-[6px] text-[#94a3b8]">{foot}</p> : null}
                    </div>
                </article>
            ))}
        </div>
    );
}

function PreviewTable({ title, columns, rows, icon: Icon = Building2, search = true }) {
    return (
        <article className="mt-3 overflow-hidden rounded-xl border border-[#c8d4de] bg-white shadow-sm">
            <div className="flex items-center justify-between gap-3 border-b border-[#e2e8f0] px-3 py-2.5">
                <p className="text-[9px] font-semibold lg:text-[10px]">{title}</p>
                {search ? <div className="hidden w-44 items-center gap-1.5 rounded-lg border border-[#c8d4de] px-2 py-1.5 text-[7px] text-[#94a3b8] sm:flex"><Search className="h-3 w-3" /> Rechercher...</div> : null}
            </div>
            <div className="hidden grid-cols-4 bg-[#f8fafc] px-3 py-2 text-[6px] font-semibold uppercase tracking-wide text-[#5f7182] sm:grid">
                {columns.map((column) => <span key={column}>{column}</span>)}
            </div>
            <div className="divide-y divide-[#eef3f7]">
                {rows.map((row, index) => (
                    <div key={row[0]} className="grid grid-cols-[1fr_auto] items-center gap-2 px-3 py-2.5 text-[8px] sm:grid-cols-4">
                        <div className="flex min-w-0 items-center gap-2"><span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#eaf4fb] text-[#00559b]"><Icon className="h-3 w-3" /></span><span className="truncate font-medium">{row[0]}</span></div>
                        <span className="hidden truncate text-[#5f7182] sm:block">{row[1]}</span>
                        <span className="hidden truncate font-medium sm:block">{row[2]}</span>
                        <span className={`rounded-full px-2 py-1 text-center text-[6px] font-medium ${index === 2 ? 'bg-[#fff4e5] text-[#b76e00]' : 'bg-[#eef8df] text-[#4d8500]'}`}>{row[3]}</span>
                    </div>
                ))}
            </div>
        </article>
    );
}

function PropertiesPreview() {
    const metrics = [
        { label: 'Total propriétés', value: '48', foot: '64 lots gérés', icon: Building2 },
        { label: 'En location', value: '36', foot: '32 occupées', icon: KeyRound, tone: 'green' },
        { label: 'En vente', value: '12', foot: '8 disponibles', icon: HomeIcon },
        { label: 'Portes libres', value: '7', foot: 'Sur 64 portes', icon: DoorOpen, tone: 'green' },
    ];
    return (
        <div className="p-3 sm:p-4 lg:p-5">
            <PreviewHeading title="Gestion des propriétés" action="Nouvelle propriété" />
            <TopMetricCards items={metrics} />
            <div className="mt-3 flex rounded-xl border border-[#c8d4de] bg-[#f1f5f9] p-1 text-[7px] font-semibold sm:text-[8px]">
                {['Toutes 48', 'En location 36', 'En vente 12', 'Référentiels'].map((tab, index) => <span key={tab} className={`rounded-lg px-3 py-1.5 ${index === 0 ? 'bg-white text-[#00559b] shadow-sm' : 'text-[#5f7182]'}`}>{tab}</span>)}
            </div>
            <PreviewTable title="Catalogue des propriétés" columns={['Référence & adresse', 'Propriétaire', 'Lots', 'Offre']} rows={[
                ['PROP-0048 · Riviera', 'Awa Koné', '8 lots', 'Location'], ['PROP-0047 · Cocody', 'Jean Kouassi', '1 lot', 'Vente'], ['PROP-0046 · Plateau', 'Fatou Bamba', '12 lots', 'Location'],
            ]} />
        </div>
    );
}

function TenantsPreview() {
    const metrics = [
        { label: 'Total locataires', value: '46', icon: UsersRound }, { label: 'Locataires actifs', value: '41', icon: UsersRound, tone: 'green' },
        { label: 'Contrats actifs', value: '41', icon: FileCheck2 }, { label: 'Contrats résiliés', value: '5', icon: FileCheck2, tone: 'red' },
    ];
    return (
        <div className="p-3 sm:p-4 lg:p-5">
            <PreviewHeading title="Gestion des locataires" action="Nouveau locataire" />
            <TopMetricCards items={metrics} sideIcon />
            <PreviewTable title="Tous les locataires" icon={UsersRound} columns={['Locataire', 'Contact', 'Propriétaire', 'Propriété']} rows={[
                ['Awa Koné · LOC-0041', '+225 07 08 09 10', 'Jean Kouassi', 'Résidence Riviera'], ['Jean-Marc Diarra · LOC-0040', '+225 05 14 22 30', 'Mariam Traoré', 'Villa Cocody'], ['Fatou Bamba · LOC-0039', '+225 01 02 03 04', 'Awa Koné', 'Studio Marcory'],
            ]} />
        </div>
    );
}

function CashPreview() {
    const metrics = [
        { label: 'Solde d’ouverture', value: '2,5 M F', icon: WalletCards }, { label: 'Total entrées', value: '8,4 M F', icon: ArrowUpRight, tone: 'green' },
        { label: 'Total sorties', value: '1,1 M F', icon: ArrowRight, tone: 'red' }, { label: 'Solde théorique', value: '9,8 M F', icon: WalletCards },
    ];
    return (
        <div className="p-3 sm:p-4 lg:p-5">
            <PreviewHeading title="Gestion financière" action="Nouveau mouvement" secondary="07/08/2026 · Actions" />
            <TopMetricCards items={metrics} />
            <div className="mt-3 flex flex-wrap gap-1.5 text-[7px] font-medium sm:text-[8px]">
                {['Transactions', 'Loyers', 'Ventes', 'Maintenance', 'Dépenses'].map((tab, index) => <span key={tab} className={`rounded-lg border px-2.5 py-1.5 ${index === 0 ? 'border-[#00559b] bg-[#00559b] text-white' : 'border-[#c8d4de] bg-white'}`}>{tab}</span>)}
            </div>
            <PreviewTable title="Mouvements" icon={WalletCards} columns={['Libellé', 'Référence', 'Montant', 'Statut']} rows={[
                ['Loyer · Awa Koné', 'ENC-0826-041', '+450 000 F', 'Validé'], ['Loyer · Jean-Marc', 'ENC-0826-040', '+725 000 F', 'Validé'], ['Frais de maintenance', 'DEP-0826-018', '-125 000 F', 'Validé'],
            ]} />
        </div>
    );
}

function MaintenancePreview() {
    const metrics = [
        { label: 'Interventions', value: '12', icon: Wrench }, { label: 'En attente', value: '2', icon: CalendarClock, tone: 'red' },
        { label: 'En cours', value: '3', icon: Wrench }, { label: 'Terminées', value: '7', icon: FileCheck2, tone: 'green' },
    ];
    const interventions = [['Plomberie', 'Riviera', 'Urgente'], ['Électricité', 'Cocody', 'Normale'], ['Climatisation', 'Plateau', 'Terminée']];
    return (
        <div className="p-3 sm:p-4 lg:p-5">
            <PreviewHeading title="Gestion de la maintenance" action="Nouveau" />
            <TopMetricCards items={metrics} sideIcon />
            <div className="mt-3 flex rounded-xl bg-slate-100 p-1 text-[7px] text-slate-500 sm:text-[8px]">
                {['Interventions', 'Maintenanciers', 'Types', 'Fonctions'].map((tab, index) => <span key={tab} className={`rounded-lg px-3 py-1.5 ${index === 0 ? 'bg-white font-semibold text-[#00559b] shadow-sm' : ''}`}>{tab}</span>)}
            </div>
            <div className="mt-3 grid gap-3 sm:grid-cols-[.85fr_1.15fr]">
                <article className="rounded-xl border border-slate-300 bg-white p-2.5 shadow-sm">
                    <div className="flex items-center justify-between"><p className="text-[9px] font-semibold">Interventions <span className="text-slate-400">12</span></p><span className="rounded-lg bg-[#00559b] px-2 py-1 text-[7px] text-white">+ Ajouter</span></div>
                    <div className="mt-2 flex items-center gap-1.5 rounded-lg border border-[#c8d4de] px-2 py-1.5 text-[7px] text-slate-400"><Search className="h-3 w-3" /> Rechercher une intervention...</div>
                    <div className="mt-2 space-y-1.5">{interventions.map(([name, place, priority], index) => <div key={name} className={`rounded-lg border p-2 ${index === 0 ? 'border-[#00559b] bg-blue-50' : 'border-slate-200'}`}><div className="flex justify-between"><p className="text-[8px] font-semibold">{name}</p><span className="text-[6px] text-[#00559b]">{priority}</span></div><p className="mt-0.5 text-[6px] text-slate-500">Résidence {place}</p></div>)}</div>
                </article>
                <article className="hidden rounded-xl border border-slate-300 bg-white p-3 shadow-sm sm:block">
                    <div className="flex items-start justify-between border-b border-slate-200 pb-2"><div><p className="text-[10px] font-semibold">Réparation plomberie</p><p className="text-[7px] text-slate-500">Résidence Riviera · Porte A04</p></div><span className="rounded-full bg-[#eaf4fb] px-2 py-1 text-[6px] text-[#00559b]">En cours</span></div>
                    <div className="mt-3 grid grid-cols-2 gap-2">{[['Propriétaire', 'Awa Koné'], ['Maintenancier', 'Koffi Services'], ['Prise en charge', 'Agence'], ['Montant', '125 000 F']].map(([label, value]) => <div key={label} className="rounded-lg bg-slate-50 p-2"><p className="text-[6px] text-slate-500">{label}</p><p className="mt-0.5 text-[8px] font-semibold">{value}</p></div>)}</div>
                </article>
            </div>
        </div>
    );
}
