@extends('admin.layouts.app')

@section('title', 'Détail agence')
@section('header_title', 'Agences')

@section('content')
    @php
        $statusLabel = match($agence->statut) {
            'active' => 'Active',
            'en_demo' => 'En démo',
            'desactive' => 'Désactivée',
            default => ucfirst(str_replace('_', ' ', $agence->statut)),
        };
        $statusBadgeClass = match($agence->statut) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'en_demo' => 'bg-amber-50 text-amber-700 border-amber-200',
            'desactive' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
        $responsable = $agence->responsable;
        $region = $agence->region;
        $ville = $agence->ville;
        $abonnement = $agence->abonnement;
        $modulesPayants = $agence->modules_payants ?? [];
    @endphp

    <section class="space-y-6 px-4 py-6 md:px-6 xl:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#00559b] text-lg font-semibold text-white shadow-sm">
                        {{ mb_strtoupper(mb_substr($agence->name ?? 'AG', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Agence</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                            {{ $agence->name ?? 'Agence sans nom' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $agence->code_agence ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.agences.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                        Retour
                    </a>
                    <a href="{{ route('admin.agences.edit', $agence->agence_id) }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50">
                        Modifier
                    </a>
                    <button type="button" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#00559b] px-4 text-sm font-medium text-white transition hover:bg-[#004980]">
                        Se connecter à l'agence
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Formule</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $abonnement?->name ?? 'Aucun abonnement' }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Statut</p>
                <p class="mt-2 inline-flex rounded-full border px-3 py-1 text-sm font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Modules actifs</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ count($modulesPayants) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Responsable</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $responsable?->name ?? 'Non défini' }}</p>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.9fr]">
            <div class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-900">Informations générales</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-200">
                                <tr><th class="w-1/3 px-6 py-4 text-left font-medium text-slate-500">Nom agence</th><td class="px-6 py-4 text-slate-900">{{ $agence->name ?? 'Non défini' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Code</th><td class="px-6 py-4 text-slate-900">{{ $agence->code_agence ?? 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Responsable</th><td class="px-6 py-4 text-slate-900">{{ $responsable?->name ?? 'Non défini' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Email responsable</th><td class="px-6 py-4 text-slate-900">{{ $responsable?->email ?? 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Téléphone responsable</th><td class="px-6 py-4 text-slate-900">{{ $responsable?->tel1 ?? 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Email agence</th><td class="px-6 py-4 text-slate-900">{{ $agence->email1 ?? 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Téléphone agence</th><td class="px-6 py-4 text-slate-900">{{ $agence->tel1 ?? 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Téléphone 2</th><td class="px-6 py-4 text-slate-900">{{ $agence->tel2 ?? 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Adresse</th><td class="px-6 py-4 text-slate-900">{{ $agence->adresse ?? 'Non définie' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Région</th><td class="px-6 py-4 text-slate-900">{{ $region?->name ?? 'Non définie' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Ville</th><td class="px-6 py-4 text-slate-900">{{ $ville?->name ?? 'Non définie' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Agence principale</th><td class="px-6 py-4 text-slate-900">{{ $agence->is_principale ? 'Oui' : 'Non' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Durée abonnement</th><td class="px-6 py-4 text-slate-900">{{ $agence->duree_mois ? $agence->duree_mois . ' mois' : 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Date création</th><td class="px-6 py-4 text-slate-900">{{ $agence->created_at ? \Carbon\Carbon::parse($agence->created_at)->format('d/m/Y H:i') : 'N/A' }}</td></tr>
                                <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Dernière mise à jour</th><td class="px-6 py-4 text-slate-900">{{ $agence->updated_at ? \Carbon\Carbon::parse($agence->updated_at)->format('d/m/Y H:i') : 'Jamais' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                @if($agence->logo || $agence->signature_responsable || $agence->signature_comptabilite || $agence->signature_marketing)
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">Médias & signatures</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @if($agence->logo)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-medium text-slate-500">Logo</p>
                                    <img src="{{ asset($agence->logo) }}" alt="Logo {{ $agence->name }}" class="mt-3 h-40 w-full rounded-xl object-contain bg-white p-3">
                                </div>
                            @endif
                            @if($agence->signature_responsable)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-medium text-slate-500">Signature responsable</p>
                                    <img src="{{ asset($agence->signature_responsable) }}" alt="Signature" class="mt-3 h-40 w-full rounded-xl object-contain bg-white p-3">
                                </div>
                            @endif
                            @if($agence->signature_comptabilite)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-medium text-slate-500">Signature comptabilité</p>
                                    <img src="{{ asset($agence->signature_comptabilite) }}" alt="Signature" class="mt-3 h-40 w-full rounded-xl object-contain bg-white p-3">
                                </div>
                            @endif
                            @if($agence->signature_marketing)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-medium text-slate-500">Signature marketing</p>
                                    <img src="{{ asset($agence->signature_marketing) }}" alt="Signature" class="mt-3 h-40 w-full rounded-xl object-contain bg-white p-3">
                                </div>
                            @endif
                        </div>
                    </article>
                @endif
            </div>

            <div class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-900">Modules payants additionnels</h3>
                    </div>
                    <div class="overflow-x-auto">
                        @if(!empty($modulesPayants) && count($modulesPayants) > 0)
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-medium">Module</th>
                                        <th class="px-6 py-4 font-medium">Type</th>
                                        <th class="px-6 py-4 font-medium">Statut</th>
                                        <th class="px-6 py-4 font-medium">Tarification</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($modulesPayants as $module)
                                        @php $moduleStatut = $module['statut'] ?? ($module->statut ?? 'Inactif'); @endphp
                                        <tr>
                                            <td class="px-6 py-4 text-slate-900">{{ $module['nom'] ?? ($module->nom ?? 'N/A') }}</td>
                                            <td class="px-6 py-4 text-slate-600">{{ $module['type'] ?? ($module->type ?? 'N/A') }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $moduleStatut === 'Actif' || $moduleStatut === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                                    {{ $moduleStatut === 'Actif' || $moduleStatut === 'active' ? 'Actif' : $moduleStatut }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">{{ $module['tarification'] ?? ($module->tarification ?? 'N/A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-6 text-sm text-slate-500">Aucun module payant activé pour cette agence.</div>
                        @endif
                    </div>
                </article>

                @if($abonnement)
                    <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 px-6 py-5">
                            <h3 class="text-lg font-semibold text-slate-900">Détails abonnement</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-slate-200">
                                    <tr><th class="w-1/2 px-6 py-4 text-left font-medium text-slate-500">Plan</th><td class="px-6 py-4 text-slate-900">{{ $abonnement->name ?? 'N/A' }}</td></tr>
                                    <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Description</th><td class="px-6 py-4 text-slate-900">{{ $abonnement->description ?? 'N/A' }}</td></tr>
                                    <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Date début</th><td class="px-6 py-4 text-slate-900">{{ $agence->abonnement_start ? \Carbon\Carbon::parse($agence->abonnement_start)->format('d/m/Y') : 'N/A' }}</td></tr>
                                    <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Date fin</th><td class="px-6 py-4 text-slate-900">{{ $agence->abonnement_end ? \Carbon\Carbon::parse($agence->abonnement_end)->format('d/m/Y') : 'N/A' }}</td></tr>
                                    <tr><th class="px-6 py-4 text-left font-medium text-slate-500">Durée</th><td class="px-6 py-4 text-slate-900">{{ $agence->duree_mois ? $agence->duree_mois . ' mois' : 'N/A' }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endif

                @if($agence->abonnementHistoriques && $agence->abonnementHistoriques->count() > 0)
                    <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 px-6 py-5">
                            <h3 class="text-lg font-semibold text-slate-900">Historique des abonnements</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-medium">Action</th>
                                        <th class="px-6 py-4 font-medium">Période</th>
                                        <th class="px-6 py-4 font-medium">Montant</th>
                                        <th class="px-6 py-4 font-medium">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($agence->abonnementHistoriques as $historique)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $historique->action === 'creation' ? 'border-sky-200 bg-sky-50 text-sky-700' : ($historique->action === 'renouvellement' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700') }}">
                                                    {{ ucfirst($historique->action) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">
                                                {{ $historique->nouvelle_date_debut ? \Carbon\Carbon::parse($historique->nouvelle_date_debut)->format('d/m/Y') : 'N/A' }}
                                                →
                                                {{ $historique->nouvelle_date_fin ? \Carbon\Carbon::parse($historique->nouvelle_date_fin)->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-slate-900">{{ number_format($historique->montant_ht ?? 0, 0, ',', ' ') }} FCFA</td>
                                            <td class="px-6 py-4 text-slate-600">{{ $historique->created_at ? \Carbon\Carbon::parse($historique->created_at)->format('d/m/Y') : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endif
            </div>
        </div>
    </section>
@endsection
