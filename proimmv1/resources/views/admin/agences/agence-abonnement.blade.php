@extends('admin.layouts.app')

@section('title', 'Abonnement agence')
@section('header_title', 'Abonnement')

@section('content')
    <section class="space-y-6 px-4 py-6 md:px-6 xl:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Abonnement</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Abonnement de l'agence</h2>
                    <p class="mt-1 text-sm text-slate-500">Consultez votre abonnement et l'historique de facturation</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50" href="{{ route('admin.agences.index') }}">
                        Retour
                    </a>
                    <button class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                        Exporter la facturation
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-amber-50/50 p-4 text-amber-900 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-amber-500"></div>
                <div>
                    <p class="text-sm font-semibold">Renouvellement automatique le 3 juin 2025</p>
                    <p class="mt-1 text-sm text-amber-800/80">
                        Votre abonnement sera renouvelé automatiquement. Pensez à vérifier vos informations de paiement.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total facturé</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">598 800 FCFA</p>
                <p class="mt-1 text-sm text-slate-500">depuis l'activation</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Paiements réussis</p>
                <p class="mt-2 text-2xl font-semibold text-sky-700">12</p>
                <p class="mt-1 text-sm text-slate-500">sur 12 tentatives</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Modules actifs</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">3 / 4</p>
                <p class="mt-1 text-sm text-slate-500">modules complémentaires</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Membre depuis</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">1 an</p>
                <p class="mt-1 text-sm text-slate-500">Mai 2024</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Plan actuel</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-900">Plan Standard</h3>
                        <p class="mt-2 text-sm text-slate-500">Accès complet · Annonces illimitées · Support prioritaire</p>
                    </div>
                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        Actif
                    </span>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Modules</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Gestion des biens</span>
                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Rapports & stats</span>
                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Multi-utilisateurs</span>
                            <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-500">Module CRM</span>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Prix</p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <p class="text-xs text-slate-500">Plan de base</p>
                                <p class="text-lg font-semibold text-slate-900">49 900 FCFA</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">+ Modules actifs</p>
                                <p class="text-lg font-semibold text-slate-900">15 000 FCFA</p>
                            </div>
                            <div class="border-t border-slate-200 pt-3">
                                <p class="text-xs text-slate-500">Total / mois</p>
                                <p class="text-2xl font-semibold text-[#00559b]">64 900 FCFA <span class="text-sm font-normal text-slate-500">/ mois</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50" type="button">
                        Gérer l'abonnement
                    </button>
                    <button class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50" type="button">
                        Renouvellement auto
                    </button>
                    <button class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-medium text-rose-700 transition hover:bg-rose-100" type="button">
                        Résilier
                    </button>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Historique des abonnements</h3>
                    <p class="mt-1 text-sm text-slate-500">Tous vos cycles de facturation</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Période</th>
                            <th class="px-6 py-4 font-medium">Modules actifs</th>
                            <th class="px-6 py-4 font-medium">Base</th>
                            <th class="px-6 py-4 font-medium">Modules</th>
                            <th class="px-6 py-4 font-medium">Total</th>
                            <th class="px-6 py-4 font-medium">Statut</th>
                            <th class="px-6 py-4 font-medium">Paiement</th>
                            <th class="px-6 py-4 font-medium">Facture</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="border-t border-slate-200">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">3 mai 2025</div>
                                <div class="text-xs text-slate-500">→ 3 juin 2025</div>
                            </td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">3 modules</span></td>
                            <td class="px-6 py-4 text-slate-600">49 900 FCFA</td>
                            <td class="px-6 py-4 text-slate-600">15 000 FCFA</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">64 900 FCFA</td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Payé</span></td>
                            <td class="px-6 py-4 text-slate-600">Orange Money</td>
                            <td class="px-6 py-4"><a href="#" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-900 transition hover:bg-slate-50">FAC-2025-05</a></td>
                        </tr>
                        <tr class="border-t border-slate-200">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">3 avr. 2025</div>
                                <div class="text-xs text-slate-500">→ 3 mai 2025</div>
                            </td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">3 modules</span></td>
                            <td class="px-6 py-4 text-slate-600">49 900 FCFA</td>
                            <td class="px-6 py-4 text-slate-600">15 000 FCFA</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">64 900 FCFA</td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Payé</span></td>
                            <td class="px-6 py-4 text-slate-600">Orange Money</td>
                            <td class="px-6 py-4"><a href="#" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-900 transition hover:bg-slate-50">FAC-2025-04</a></td>
                        </tr>
                        <tr class="border-t border-slate-200">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">3 mars 2025</div>
                                <div class="text-xs text-slate-500">→ 3 avr. 2025</div>
                            </td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">2 modules</span></td>
                            <td class="px-6 py-4 text-slate-600">49 900 FCFA</td>
                            <td class="px-6 py-4 text-slate-600">8 000 FCFA</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">57 900 FCFA</td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Payé</span></td>
                            <td class="px-6 py-4 text-slate-600">Wave</td>
                            <td class="px-6 py-4"><a href="#" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-900 transition hover:bg-slate-50">FAC-2025-03</a></td>
                        </tr>
                        <tr class="border-t border-slate-200">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">3 fév. 2025</div>
                                <div class="text-xs text-slate-500">→ 3 mars 2025</div>
                            </td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">2 modules</span></td>
                            <td class="px-6 py-4 text-slate-600">49 900 FCFA</td>
                            <td class="px-6 py-4 text-slate-600">8 000 FCFA</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">57 900 FCFA</td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700">Échec</span></td>
                            <td class="px-6 py-4 text-slate-600">Wave</td>
                            <td class="px-6 py-4 text-slate-500">—</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>
@endsection
