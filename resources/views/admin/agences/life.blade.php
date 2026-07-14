@extends('admin.layouts.app')

@section('title', 'Vie de l\'agence - ' . $agence['nom'])
@section('header_title', 'Agences')

@section('content')
    <section class="space-y-6 px-4 py-6 md:px-6 xl:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#00559b] text-lg font-semibold text-white shadow-sm">
                        {{ substr($agence['nom'], 0, 2) }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Agence</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $agence['nom'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Code : {{ $agence['code'] }} | Historique des activités, tickets et événements</p>
                    </div>
                </div>
                <a href="{{ route('admin.agences.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Historique</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">Activités récentes</h3>
                    </div>
                    <button class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50" onclick="window.location.reload()">
                        Actualiser
                    </button>
                </div>

                <div class="space-y-3 p-6">
                    @foreach($activities as $activity)
                        <div class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-full"
                                  style="background:
                                      @if($activity['color'] == 'blue') #00559b
                                      @elseif($activity['color'] == 'green') #76c206
                                      @elseif($activity['color'] == 'red') #dc2626
                                      @elseif($activity['color'] == 'yellow') #f59e0b
                                      @else #94a3b8
                                      @endif;">
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900">{{ $activity['title'] }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $activity['description'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Par <strong>{{ $activity['user'] }}</strong></p>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $activity['date']->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <aside class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Statistiques</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900">Chiffres clés de l'agence</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Locataires</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['nb_locataires'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Propriétaires</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['nb_proprietaires'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Biens</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['nb_biens'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Lots</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['nb_lots'] ?? 0 }}</p>
                        </div>
                    </div>

                    <h3 class="mt-6 text-lg font-semibold text-slate-900">Support</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Tickets</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['nb_tickets'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Tickets résolus</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['nb_tickets_resolus'] ?? 0 }}</p>
                        </div>
                    </div>
                </article>
            </aside>
        </div>
    </section>
@endsection
