import { Head, Link } from '@inertiajs/react';
import { ArrowRight, ArrowUpRight, Check, Minus, Plus } from 'lucide-react';
import { useState } from 'react';

import PublicLayout from './PublicLayout';

const gallery = [
    {
        src: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=85&w=1000',
        alt: 'Équipe réunie autour d’une table de travail',
    },
    {
        src: 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&q=85&w=1000',
        alt: 'Collaborateurs de Pros Immobilier',
    },
    {
        src: 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&q=85&w=1000',
        alt: 'Réunion de travail dans une agence',
    },
    {
        src: 'https://images.unsplash.com/photo-1556761175-4b46a572b786?auto=format&fit=crop&q=85&w=1000',
        alt: 'Équipe échangeant dans un bureau',
    },
];

const commitments = [
    'Une plateforme pensée pour les réalités des agences',
    'Des données centralisées et accessibles à tout moment',
    'Un accompagnement humain à chaque étape',
];

const faqs = [
    {
        question: 'Pourquoi choisir Pros Immobilier ?',
        answer: 'La plateforme rassemble la gestion des biens, des locataires, des loyers, des reversements et des interventions dans un espace unique, clair et sécurisé.',
    },
    {
        question: 'À qui s’adresse la plateforme ?',
        answer: 'Pros Immobilier s’adresse aux agences immobilières de toutes tailles qui souhaitent structurer leurs opérations et offrir un meilleur suivi à leurs clients.',
    },
    {
        question: 'Mes données sont-elles sécurisées ?',
        answer: 'Oui. Chaque agence dispose de son propre espace isolé et les accès peuvent être organisés selon les responsabilités de chaque collaborateur.',
    },
    {
        question: 'Puis-je essayer la solution avant de m’abonner ?',
        answer: 'Oui. Vous pouvez demander une démonstration afin de découvrir les fonctionnalités et vérifier qu’elles correspondent aux besoins de votre agence.',
    },
    {
        question: 'L’équipe accompagne-t-elle la prise en main ?',
        answer: 'Oui. Notre équipe vous guide dans la configuration initiale et reste disponible pour vous aider à adopter la plateforme sereinement.',
    },
    {
        question: 'Puis-je accéder à Pros Immobilier en déplacement ?',
        answer: 'Oui. La plateforme est accessible depuis un navigateur sur ordinateur, tablette ou téléphone, partout où vous disposez d’une connexion internet.',
    },
];

export default function About() {
    const [openFaq, setOpenFaq] = useState(0);

    return (
        <PublicLayout>
            <Head title="À propos" />

            <main className="overflow-hidden bg-[#f5f8fc] text-[#111f3d]">
                {/* INTRODUCTION */}
                <section className="relative px-5 pb-24 pt-20 sm:px-6 lg:pb-32 lg:pt-28">
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute -left-40 -top-56 h-[540px] w-[540px] rounded-full border border-[#76c206]/50"
                    />
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute -right-44 top-10 h-[620px] w-[620px] rounded-full border border-[#00559b]/25"
                    />
                    

                    <div className="relative mx-auto max-w-5xl">
                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-[#67a900]">
                            Notre histoire
                        </p>

                        <h1 className="mt-7 max-w-4xl text-balance text-5xl font-medium leading-[0.98] tracking-[-0.045em] text-[#0b1730] sm:text-6xl lg:text-[5rem]">
                            Construire l’avenir de la
                            <span className="relative block w-fit">
                                gestion immobilière
                            </span>

                        </h1>

                        <div className="mt-12 max-w-3xl space-y-6 text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                            <p>
                                Pros Immobilier est né d’un constat simple : les
                                agences perdent encore trop de temps entre les
                                cahiers, les feuilles de calcul et des outils qui
                                ne communiquent pas entre eux. Nous avons créé la
                                plateforme qui rassemble enfin tout leur travail.
                            </p>
                            <p>
                                Notre ambition est de rendre la gestion locative
                                plus fluide, plus fiable et plus transparente.
                                Chaque fonctionnalité répond à une réalité du
                                terrain, du suivi d’un loyer au reversement d’un
                                propriétaire, en passant par la maintenance.
                            </p>
                            <p>
                                Nous avançons aux côtés des professionnels de
                                l’immobilier pour bâtir un outil utile aujourd’hui
                                et capable d’accompagner leur croissance demain.
                            </p>
                        </div>

                        <Link
                            href="/contact"
                            className="mt-9 inline-flex items-center gap-3 rounded-lg bg-[#76c206] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#66aa04]"
                        >
                            Rencontrer notre équipe
                            <ArrowUpRight className="h-4 w-4" />
                        </Link>
                    </div>
                </section>

                {/* TEAM GALLERY */}
                <section className="pb-24 lg:pb-32">
                    <div className="mx-auto max-w-5xl px-5 sm:px-6">
                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-[#67a900]">
                            Derrière la plateforme
                        </p>
                        <h2 className="mt-4 text-4xl font-medium tracking-[-0.04em] text-[#0b1730] sm:text-5xl">
                            Une équipe engagée
                        </h2>
                    </div>

                    <div className="mt-10 grid grid-cols-2 gap-3 px-3 sm:grid-cols-4 sm:gap-4 lg:px-0">
                        {gallery.map((image, index) => (
                            <figure
                                key={image.src}
                                className={`overflow-hidden bg-slate-200 ${
                                    index % 2 === 0 ? 'sm:translate-y-5' : ''
                                }`}
                            >
                                <img
                                    src={image.src}
                                    alt={image.alt}
                                    className="h-56 w-full object-cover grayscale-[15%] transition duration-500 hover:scale-105 hover:grayscale-0 sm:h-72 lg:h-80"
                                />
                            </figure>
                        ))}
                    </div>
                </section>

                {/* PURPOSE */}
                <section className="border-y border-[#0b1730]/10 px-5 py-20 sm:px-6 lg:py-28">
                    <div className="mx-auto grid max-w-5xl gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:gap-24">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-[#67a900]">
                                Notre mission
                            </p>
                            <h2 className="mt-5 text-4xl font-medium leading-tight tracking-[-0.04em] text-[#0b1730] sm:text-5xl">
                                Simplifier pour mieux gérer.
                            </h2>
                        </div>

                        <div>
                            <p className="text-xl leading-8 text-[#0b1730] sm:text-2xl sm:leading-10">
                                Donner aux agences une vision claire de leur
                                activité et les libérer des tâches répétitives,
                                afin qu’elles puissent se concentrer sur leurs
                                clients et leur développement.
                            </p>

                            <div className="mt-10 divide-y divide-[#0b1730]/10 border-y border-[#0b1730]/10">
                                {commitments.map((commitment) => (
                                    <div
                                        key={commitment}
                                        className="flex items-center gap-4 py-5 text-sm font-semibold sm:text-base"
                                    >
                                        <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#76c206] text-white">
                                            <Check className="h-4 w-4" />
                                        </span>
                                        {commitment}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="px-5 py-24 sm:px-6 lg:py-32">
                    <div className="mx-auto grid max-w-5xl gap-12 lg:grid-cols-[240px_1fr] lg:gap-20">
                        <div>
                            <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.24em] text-[#67a900]">
                                <span className="h-1.5 w-1.5 bg-[#76c206]" />
                                FAQ
                            </p>
                            <h2 className="mt-5 text-4xl font-medium leading-[1.05] tracking-[-0.04em] text-[#0b1730]">
                                Questions fréquentes
                            </h2>
                        </div>

                        <div className="border-t border-[#0b1730]/10">
                            {faqs.map((faq, index) => {
                                const isOpen = openFaq === index;

                                return (
                                    <div
                                        key={faq.question}
                                        className="border-b border-[#0b1730]/10"
                                    >
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setOpenFaq(isOpen ? null : index)
                                            }
                                            className="flex w-full items-center justify-between gap-6 py-5 text-left text-base font-semibold text-[#0b1730] sm:py-6 sm:text-lg"
                                            aria-expanded={isOpen}
                                        >
                                            {faq.question}
                                            {isOpen ? (
                                                <Minus className="h-5 w-5 shrink-0" />
                                            ) : (
                                                <Plus className="h-5 w-5 shrink-0" />
                                            )}
                                        </button>

                                        {isOpen ? (
                                            <p className="max-w-2xl pb-6 pr-10 text-sm leading-7 text-slate-600 sm:text-base">
                                                {faq.answer}
                                            </p>
                                        ) : null}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* FINAL CTA */}
                <section className="px-5 pb-24 sm:px-6 lg:pb-32">
                    <div className="mx-auto max-w-5xl">
                        <div className="flex flex-col justify-between gap-8 border-t border-[#0b1730]/10 pt-12 sm:flex-row sm:items-end">
                            <div>
                                <h2 className="text-4xl font-medium tracking-[-0.04em] text-[#0b1730] sm:text-6xl">
                                    Lancez-vous en
                                    <span className="text-[#76c206]"> quelques minutes</span>
                                </h2>
                                <p className="mt-5 max-w-xl leading-7 text-slate-600">
                                    Découvrez une nouvelle façon de piloter votre
                                    agence avec des informations fiables et un
                                    accompagnement dédié.
                                </p>
                            </div>

                            <div className="flex shrink-0 flex-wrap gap-3">
                                <Link
                                    href="/contact"
                                    className="inline-flex items-center gap-2 rounded-lg bg-[#76c206] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#66aa04]"
                                >
                                    Demander une démo
                                    <ArrowUpRight className="h-4 w-4" />
                                </Link>
                                <Link
                                    href="/tarifs"
                                    className="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-bold text-[#0b1730] transition hover:bg-slate-100"
                                >
                                    Voir les offres
                                    <ArrowRight className="h-4 w-4" />
                                </Link>
                            </div>
                        </div>

                        <div className="mt-12 h-56 overflow-hidden bg-slate-200 sm:h-72">
                            <img
                                src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&q=85&w=1800"
                                alt="Immeubles modernes au coucher du soleil"
                                className="h-full w-full object-cover"
                            />
                        </div>
                    </div>
                </section>
            </main>
        </PublicLayout>
    );
}
