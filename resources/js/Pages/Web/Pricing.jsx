import { useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    Building2,
    Calculator,
    Check,
    Clock3,
    Headphones,
    Layers,
    ShieldCheck,
    Sparkles,
    Users,
    X,
} from 'lucide-react';

import PublicLayout from './PublicLayout';

const includedFeatures = [
    {
        label: 'Gestion des biens, bâtiments et lots',
        icon: Building2,
    },
    {
        label: 'Gestion des propriétaires et locataires',
        icon: Users,
    },
    {
        label: 'Suivi des loyers et paiements',
        icon: BadgeCheck,
    },
    {
        label: 'Gestion des impayés',
        icon: Clock3,
    },
    {
        label: 'Reversements aux propriétaires',
        icon: Layers,
    },
    {
        label: 'Tableaux de bord et statistiques',
        icon: Sparkles,
    },
    {
        label: 'Gestion des maintenances',
        icon: Headphones,
    },
];

const guarantees = [
    {
        icon: Clock3,
        title: 'Facturation mensuelle',
        text: 'Un paiement simple chaque mois, sans engagement de longue durée.',
    },
    {
        icon: ShieldCheck,
        title: 'Données sécurisées',
        text: 'Chaque agence dispose de son propre espace sécurisé et isolé.',
    },
    {
        icon: Headphones,
        title: 'Accompagnement dédié',
        text: 'Notre équipe vous accompagne dans la prise en main de la plateforme.',
    },
];

const faqs = [
    {
        q: 'Y a-t-il un engagement de durée ?',
        a: "Non. L'abonnement est mensuel et sans engagement. Vous pouvez l'arrêter à tout moment.",
    },
    {
        q: 'Puis-je tester la plateforme avant de m’abonner ?',
        a: 'Oui. Vous pouvez demander une démonstration gratuite afin de découvrir les principales fonctionnalités.',
    },
    {
        q: 'Les modules complémentaires sont-ils obligatoires ?',
        a: 'Non. Les modules sont optionnels. Vous ajoutez uniquement ceux dont votre agence a besoin.',
    },
];

const formatMoney = (value) =>
    `${new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))} FCFA`;

export default function Pricing({ tarifs = {} }) {
    const plan = tarifs.plan ?? {};
    const modules = Array.isArray(tarifs.modules) ? tarifs.modules : [];

    const price = formatMoney(plan.prix_mensuel);

    const [selectedModules, setSelectedModules] = useState([]);

    const toggleModule = (moduleId) => {
        setSelectedModules((prev) =>
            prev.includes(moduleId)
                ? prev.filter((id) => id !== moduleId)
                : [...prev, moduleId],
        );
    };

    const modulesTotal = useMemo(() => {
        return modules
            .filter((m) => selectedModules.includes(m.id ?? m.label))
            .reduce((sum, m) => sum + Number(m.prix_mensuel ?? 0), 0);
    }, [modules, selectedModules]);

    const totalMensuel = Number(plan.prix_mensuel ?? 0) + modulesTotal;

    return (
        <PublicLayout>
            <Head title="Tarifs" />

            <main className="bg-white">
                {/* HERO */}
                <section className="relative overflow-hidden bg-[#0b1730] px-5 pb-40 pt-24 text-white lg:pb-48 lg:pt-32">
                    {/* GRID BACKGROUND */}
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-0 opacity-[0.06]"
                        style={{
                            backgroundImage:
                                'linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px)',
                            backgroundSize: '56px 56px',
                        }}
                    />

                    <div className="relative mx-auto max-w-4xl text-center">
                        <h1 className="mt-7 text-balance text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
                            Tout ce dont votre agence a besoin,
                            <span className="block text-[#8fd222]">
                                dans un seul abonnement
                            </span>
                        </h1>
                    </div>
                </section>

                {/* PRICING SUMMARY */}
                <section className="relative px-5">
                    <div className="mx-auto -mt-24 max-w-4xl">
                        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                            <div className="grid md:grid-cols-[1fr_auto] md:items-center">
                                {/* LEFT */}
                                <div className="p-6 sm:p-7">
                                    <div className="flex flex-wrap items-center gap-3">
                                        <h2 className="text-xl font-extrabold text-[#0b1730]">
                                            {plan.nom || 'Pros Immobilier'}
                                        </h2>

                                        <span className="rounded-full bg-[#8fd222]/15 px-3 py-1 text-xs font-bold text-[#5b9900]">
                                            Sans engagement
                                        </span>
                                    </div>

                                    <p className="mt-2 text-sm text-slate-500">
                                        Toutes les fonctionnalités essentielles pour gérer votre agence.
                                    </p>

                                    <div className="mt-5 flex flex-wrap gap-4">
                                        <span className="flex items-center gap-2 text-sm text-slate-600">
                                            <Check className="h-4 w-4 text-[#67a900]" />
                                            Fonctionnalités essentielles incluses
                                        </span>

                                        <span className="flex items-center gap-2 text-sm text-slate-600">
                                            <Check className="h-4 w-4 text-[#67a900]" />
                                            Accompagnement inclus
                                        </span>
                                    </div>
                                </div>

                                {/* RIGHT */}
                                <div className="border-t border-slate-100 bg-slate-50 p-6 md:min-w-[290px] md:border-l md:border-t-0">
                                    <div className="flex items-end gap-2">
                                        <span className="text-3xl font-extrabold tracking-tight text-[#0b1730]">
                                            {price}
                                        </span>

                                        <span className="pb-1 text-sm text-slate-500">
                                            / mois
                                        </span>
                                    </div>

                                    <div className="mt-5 flex gap-2">
                                        <Link
                                            href="/inscription-agence"
                                            className="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#00559b] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#004785]"
                                        >
                                            Commencer
                                            <ArrowRight className="h-4 w-4" />
                                        </Link>

                                        <Link
                                            href="/contact"
                                            className="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-[#0b1730] transition hover:bg-slate-50"
                                        >
                                            Démo
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* FEATURES & MODULES (combined) */}
                <section className="px-5 py-20 lg:py-28">
                    <div className="mx-auto max-w-5xl">
                        <div className="mb-10 max-w-2xl">
                            <p className="text-sm font-extrabold uppercase tracking-[0.2em] text-[#76c206]">
                                Fonctionnalités & modules
                            </p>

                            <h2 className="mt-3 text-balance text-3xl font-extrabold tracking-tight text-[#0b1730] sm:text-4xl">
                                Tout ce qui est inclus, et ce que vous
                                pouvez ajouter
                            </h2>

                            <p className="mt-4 leading-7 text-slate-600">
                                Toutes les fonctionnalités essentielles sont
                                incluses dans l'abonnement de base. Ajoutez
                                ensuite uniquement les modules utiles à
                                votre activité et suivez le tarif en temps
                                réel.
                            </p>
                        </div>

                        <div
                            className={`grid gap-6 ${modules.length > 0 ? 'lg:grid-cols-[1fr_340px]' : ''}`}
                        >
                            {/* LEFT: included features + optional modules */}
                            <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                                {/* DESKTOP HEADER */}
                                <div className="hidden grid-cols-[1fr_260px] border-b border-slate-200 bg-slate-50 sm:grid">
                                    <div className="flex items-center p-6">
                                        <span className="text-sm font-bold text-slate-500">
                                            Inclus dans l'abonnement
                                        </span>
                                    </div>

                                    <div className="border-l border-slate-200 p-6 text-center">
                                        <p className="font-extrabold text-[#0b1730]">
                                            {plan.nom || 'Pros Immobilier'}
                                        </p>

                                        <p className="mt-1 text-sm font-semibold text-[#00559b]">
                                            {price} / mois
                                        </p>
                                    </div>
                                </div>

                                {/* INCLUDED FEATURES (static) */}
                                <div>
                                    {includedFeatures.map(
                                        ({ label, icon: Icon }, index) => (
                                            <div
                                                key={label}
                                                className="grid grid-cols-[1fr_auto] items-center border-b border-slate-100 sm:grid-cols-[1fr_260px]"
                                            >
                                                <div className="flex items-center gap-3 p-5 sm:p-6">
                                                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#00559b]/8 text-[#00559b]">
                                                        <Icon className="h-4 w-4" />
                                                    </span>

                                                    <span className="text-sm font-medium leading-6 text-slate-700 sm:text-base">
                                                        {label}
                                                    </span>
                                                </div>

                                                <div className="flex h-full items-center justify-center border-l border-slate-100 px-5 py-6 sm:px-6">
                                                    <span className="flex h-8 w-8 items-center justify-center rounded-full bg-[#8fd222]/15 text-[#5b9900]">
                                                        <Check className="h-4 w-4 stroke-[3]" />
                                                    </span>

                                                    <span className="sr-only">
                                                        Inclus
                                                    </span>
                                                </div>
                                            </div>
                                        ),
                                    )}
                                </div>

                                {/* MODULES SUB-HEADER */}
                                {modules.length > 0 ? (
                                    <div className="border-b border-t border-slate-200 bg-slate-50 px-5 py-3 sm:px-6">
                                        <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                                            Modules en option
                                        </p>
                                    </div>
                                ) : null}

                                {/* OPTIONAL MODULES (interactive) */}
                                <div>
                                    {modules.map((module, index) => {
                                            const id = module.id ?? module.label;
                                            const isFree = !(
                                                Number(module.prix_mensuel) > 0
                                            );
                                            const isChecked =
                                                isFree ||
                                                selectedModules.includes(id);

                                            return (
                                                <button
                                                    key={id}
                                                    type="button"
                                                    disabled={isFree}
                                                    onClick={() =>
                                                        toggleModule(id)
                                                    }
                                                    className={`flex w-full items-center justify-between gap-4 p-5 text-left transition ${
                                                        index !==
                                                        modules.length - 1
                                                            ? 'border-b border-slate-100'
                                                            : ''
                                                    } ${
                                                        isFree
                                                            ? 'cursor-default opacity-70'
                                                            : 'hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="flex items-center gap-3">
                                                        <span
                                                            className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-md border-2 transition ${
                                                                isChecked
                                                                    ? 'border-[#8fd222] bg-[#8fd222]'
                                                                    : 'border-slate-300 bg-white'
                                                            }`}
                                                        >
                                                            {isChecked ? (
                                                                <Check className="h-4 w-4 stroke-[3] text-[#0b1730]" />
                                                            ) : null}
                                                        </span>

                                                        <div>
                                                            <p className="text-sm font-bold text-[#0b1730]">
                                                                {module.label}
                                                            </p>
                                                            {module.description ? (
                                                                <p className="mt-0.5 text-xs leading-5 text-slate-500">
                                                                    {
                                                                        module.description
                                                                    }
                                                                </p>
                                                            ) : null}
                                                        </div>
                                                    </div>

                                                    <span className="shrink-0 text-sm font-bold text-[#00559b]">
                                                        {isFree
                                                            ? 'Inclus'
                                                            : `+${formatMoney(module.prix_mensuel)}`}
                                                    </span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* TOTAL SUMMARY */}
                                {modules.length > 0 ? (
                                <div className="h-fit rounded-3xl border border-slate-200 bg-[#0b1730] p-6 lg:sticky lg:top-24 lg:self-start">
                                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-[#8fd222]">
                                        <Calculator className="h-5 w-5" />
                                    </span>

                                    <p className="mt-4 text-sm font-semibold text-white/60">
                                        Votre estimation
                                    </p>

                                    <div className="mt-1 flex items-end gap-2">
                                        <span className="text-4xl font-extrabold text-white">
                                            {formatMoney(totalMensuel)}
                                        </span>
                                        <span className="pb-1 text-sm text-white/50">
                                            / mois
                                        </span>
                                    </div>

                                    {selectedModules.length > 0 ? (
                                        <div className="mt-5 space-y-2 border-t border-white/10 pt-4">
                                            {modules
                                                .filter((m) =>
                                                    selectedModules.includes(
                                                        m.id ?? m.label,
                                                    ),
                                                )
                                                .map((m) => (
                                                    <div
                                                        key={m.id ?? m.label}
                                                        className="flex items-center justify-between text-xs text-white/70"
                                                    >
                                                        <span>{m.label}</span>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                toggleModule(
                                                                    m.id ??
                                                                        m.label,
                                                                )
                                                            }
                                                            className="text-white/40 hover:text-white"
                                                        >
                                                            <X className="h-3.5 w-3.5" />
                                                        </button>
                                                    </div>
                                                ))}
                                        </div>
                                    ) : null}

                                    <Link
                                        href="/inscription-agence"
                                        className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#8fd222] px-5 py-3 text-sm font-bold text-[#0b1730] transition hover:bg-[#7cc00f]"
                                    >
                                        Commencer
                                        <ArrowRight className="h-4 w-4" />
                                    </Link>
                                </div>
                                ) : null}
                            </div>
                        </div>
                    </section>

                {/* GUARANTEES */}
                <section className="px-5 py-20 lg:py-24">
                    <div className="mx-auto max-w-5xl">
                        <div className="grid gap-5 sm:grid-cols-3">
                            {guarantees.map(
                                ({ icon: Icon, title, text }) => (
                                    <article
                                        key={title}
                                        className="rounded-2xl border border-slate-200 bg-white p-6"
                                    >
                                        <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#00559b]/10 text-[#00559b]">
                                            <Icon className="h-5 w-5" />
                                        </span>

                                        <h3 className="mt-4 font-bold text-[#0b1730]">
                                            {title}
                                        </h3>

                                        <p className="mt-2 text-sm leading-6 text-slate-600">
                                            {text}
                                        </p>
                                    </article>
                                ),
                            )}
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="px-5 pb-24">
                    <div className="mx-auto max-w-3xl">
                        <div className="text-center">
                            <p className="text-sm font-extrabold uppercase tracking-[0.2em] text-[#76c206]">
                                FAQ
                            </p>

                            <h2 className="mt-3 text-3xl font-extrabold tracking-tight text-[#0b1730]">
                                Questions fréquentes
                            </h2>
                        </div>

                        <div className="mt-8 divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            {faqs.map(({ q, a }) => (
                                <div key={q} className="p-6">
                                    <h3 className="font-bold text-[#0b1730]">
                                        {q}
                                    </h3>

                                    <p className="mt-2 text-sm leading-6 text-slate-600">
                                        {a}
                                    </p>
                                </div>
                            ))}
                        </div>

                        {/* FINAL CTA */}
                        <div className="mt-10 rounded-3xl bg-[#0b1730] p-8 text-center sm:p-10">
                            <h3 className="text-2xl font-extrabold text-white">
                                Découvrez Pros Immobilier
                            </h3>

                            <p className="mx-auto mt-3 max-w-lg text-sm leading-6 text-white/65">
                                Besoin de voir la plateforme avant de commencer ?
                                Demandez une démonstration et découvrez son
                                fonctionnement avec notre équipe.
                            </p>

                            <div className="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                                <Link
                                    href="/inscription-agence"
                                    className="inline-flex items-center justify-center gap-2 rounded-lg bg-[#8fd222] px-6 py-3.5 text-sm font-bold text-[#0b1730] transition hover:bg-[#7cc00f]"
                                >
                                    Créer mon agence
                                    <ArrowRight className="h-4 w-4" />
                                </Link>

                                <Link
                                    href="/contact"
                                    className="inline-flex items-center justify-center rounded-lg border border-white/20 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10"
                                >
                                    Demander une démonstration
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </PublicLayout>
    );
}
