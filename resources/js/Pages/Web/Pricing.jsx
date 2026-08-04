import { Head, Link } from '@inertiajs/react';
import { ArrowRight, BadgeCheck, Check, Clock3, Headphones, ShieldCheck } from 'lucide-react';

import PublicLayout from './PublicLayout';

const includedFeatures = [
    'Gestion des biens, bâtiments et lots',
    'Gestion des propriétaires et locataires',
    'Suivi des loyers, paiements et impayés',
    'Gestion des reversements aux propriétaires',
    'Tableaux de bord et statistiques',
    'Gestion des maintenances et du support',
];

const formatMoney = (value) =>
    `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0))} FCFA`;

export default function Pricing({ tarifs = {} }) {
    const plan = tarifs.plan ?? {};
    const modules = Array.isArray(tarifs.modules) ? tarifs.modules : [];
    const price = formatMoney(plan.prix_mensuel);

    return (
        <PublicLayout>
            <Head title="Tarifs" />

            <main>
                <section className="relative overflow-hidden bg-[#f4f9fd] px-5 py-20 lg:py-28">
                    <div className="absolute -right-24 -top-32 h-96 w-96 rounded-full bg-[#76c206]/10" />
                    <div className="absolute -bottom-40 -left-28 h-96 w-96 rounded-full bg-[#00559b]/10" />
                    <div className="relative mx-auto max-w-4xl text-center">
                        <span className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-[#00559b] shadow-sm">
                            <BadgeCheck className="h-4 w-4 text-[#76c206]" /> Une formule simple et transparente
                        </span>
                        <h1 className="mt-6 text-4xl font-extrabold tracking-tight text-[#111f3d] sm:text-5xl lg:text-6xl">
                            Un seul abonnement pour gérer toute votre agence
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                            Profitez de toutes les fonctions essentielles de Pros Immobilier avec un abonnement mensuel unique, sans formule compliquée.
                        </p>
                    </div>
                </section>

                <section className="px-5 py-20">
                    <div className="mx-auto grid max-w-6xl items-start gap-10 lg:grid-cols-[0.85fr_1.15fr]">
                        <div>
                            <p className="text-sm font-extrabold uppercase tracking-[0.2em] text-[#76c206]">Tarification unique</p>
                            <h2 className="mt-3 text-3xl font-extrabold text-[#111f3d] sm:text-4xl">Simple à comprendre, facile à prévoir</h2>
                            <p className="mt-5 leading-7 text-slate-600">
                                Le montant présenté provient directement de la configuration d’abonnement de l’application. Toute modification effectuée par l’administration est automatiquement répercutée ici.
                            </p>
                            <div className="mt-8 space-y-4">
                                <div className="flex items-start gap-3"><Clock3 className="mt-0.5 h-5 w-5 text-[#00559b]" /><p className="text-sm leading-6 text-slate-600"><strong className="text-[#111f3d]">Facturation mensuelle :</strong> maîtrisez vos dépenses sans engagement complexe.</p></div>
                                <div className="flex items-start gap-3"><ShieldCheck className="mt-0.5 h-5 w-5 text-[#00559b]" /><p className="text-sm leading-6 text-slate-600"><strong className="text-[#111f3d]">Données sécurisées :</strong> chaque agence accède uniquement à son propre espace.</p></div>
                                <div className="flex items-start gap-3"><Headphones className="mt-0.5 h-5 w-5 text-[#00559b]" /><p className="text-sm leading-6 text-slate-600"><strong className="text-[#111f3d]">Accompagnement :</strong> notre équipe vous aide à prendre en main la plateforme.</p></div>
                            </div>
                        </div>

                        <article className="overflow-hidden rounded-[2rem] border border-[#00559b]/15 bg-white shadow-[0_24px_70px_rgba(17,31,61,0.12)]">
                            <div className="bg-[#111f3d] px-7 py-8 text-white sm:px-10">
                                <div className="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#76c206]">Abonnement agence</p>
                                        <h2 className="mt-2 text-3xl font-extrabold">{plan.nom || 'Pros Immobilier'}</h2>
                                    </div>
                                    <span className="rounded-full bg-[#76c206] px-4 py-2 text-xs font-extrabold uppercase tracking-wide">Formule unique</span>
                                </div>
                                {plan.description ? <p className="mt-4 max-w-xl text-sm leading-6 text-white/70">{plan.description}</p> : null}
                                <div className="mt-7 flex items-end gap-2">
                                    <span className="text-4xl font-extrabold sm:text-5xl">{price}</span>
                                    <span className="pb-1 text-sm text-white/60">/ mois</span>
                                </div>
                            </div>

                            <div className="p-7 sm:p-10">
                                <h3 className="font-bold text-[#111f3d]">Ce que votre abonnement vous permet de gérer</h3>
                                <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                    {includedFeatures.map((feature) => (
                                        <div key={feature} className="flex items-start gap-3 text-sm leading-6 text-slate-600">
                                            <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#76c206]/15 text-[#5b9900]"><Check className="h-3.5 w-3.5" /></span>
                                            {feature}
                                        </div>
                                    ))}
                                </div>

                                {modules.length > 0 ? (
                                    <div className="mt-7 rounded-2xl bg-[#f4f9fd] p-5">
                                        <p className="text-sm font-bold text-[#111f3d]">Modules complémentaires disponibles</p>
                                        <div className="mt-3 flex flex-wrap gap-2">
                                            {modules.map((module) => (
                                                <span key={module.id ?? module.label} className="rounded-full border border-[#00559b]/10 bg-white px-3 py-1.5 text-xs font-semibold text-[#00559b]">
                                                    {module.label}{Number(module.prix_mensuel) > 0 ? ` · ${formatMoney(module.prix_mensuel)}/mois` : ''}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}

                                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                    <Link href="/inscription-agence" className="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-[#00559b] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#00457c]">
                                        Créer mon agence <ArrowRight className="h-4 w-4" />
                                    </Link>
                                    <Link href="/contact" className="inline-flex flex-1 items-center justify-center rounded-full border border-[#00559b]/20 px-6 py-3.5 text-sm font-bold text-[#00559b] transition hover:bg-[#eef7fd]">
                                        Demander une démonstration
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>
        </PublicLayout>
    );
}
