<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Fiche de reversement</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 14px; background: #f5f7fa; color: #17324d; font-family: Arial, sans-serif; font-size: 13px; }
        .sheet { position: relative; isolation: isolate; max-width: 1000px; margin: auto; padding: 18px; overflow: hidden; background: white; border: 1px solid #e1e7ee; border-radius: 16px; }
        .watermark { position: absolute; z-index: -1; top: 50%; left: 50%; width: min(45%, 360px); max-height: 55%; object-fit: contain; opacity: .07; transform: translate(-50%, -50%); }
        .brand-logo { display: block; max-width: 70px; max-height: 58px; margin-bottom: 7px; object-fit: contain; }
        h1 { margin: 0 0 4px; color: #075a91; font-size: 21px; }
        .muted { color: #64748b; }
        .info, .totals { display: grid; gap: 8px; margin-top: 14px; }
        .info { grid-template-columns: repeat(3, 1fr); }
        .totals { grid-template-columns: repeat(3, 1fr); }
        .box { padding: 10px; background: #f5f8fa; border: 1px solid #e1e7ee; border-radius: 10px; }
        .amount { display: block; margin-top: 4px; color: #075a91; font-size: 15px; font-weight: bold; }
        .table-wrap { margin-top: 15px; overflow-x: auto; }
        table { width: 100%; min-width: 720px; border-collapse: collapse; }
        th { padding: 8px 6px; color: white; background: #075a91; text-align: left; }
        td { padding: 8px 6px; border-bottom: 1px solid #e1e7ee; }
        .right { text-align: right; }
        .orientation { display: none; margin-bottom: 10px; padding: 9px; color: #075a91; background: #eaf4fb; border-radius: 9px; text-align: center; }
        @media (orientation: portrait) { .orientation { display: block; } }
        @media (max-width: 700px) {
            body { padding: 8px; font-size: 12px; }
            .sheet { padding: 12px; border-radius: 12px; }
            .info { grid-template-columns: repeat(2, 1fr); }
            .totals { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="orientation">Pour une meilleure lecture, tournez votre téléphone en mode paysage.</div>
<main class="sheet">
    @if($documentLogo)
        <img class="watermark" src="{{ $documentLogo }}" alt="">
        <img class="brand-logo" src="{{ $documentLogo }}" alt="Logo">
    @endif
    <h1>Fiche de reversement</h1>
    <div class="muted">{{ $reversement->agence?->name ?? 'Agence immobilière' }}</div>

    <section class="info">
        <div class="box"><strong>Lot</strong><br>{{ $reversement->lot?->name ?? '—' }} — Îlot {{ $reversement->lot?->num_ilot ?? '—' }} / Lot {{ $reversement->lot?->num_lot ?? '—' }}</div>
        <div class="box"><strong>Période</strong><br>{{ optional($reversement->periode_debut)->format('d/m/Y') }} au {{ optional($reversement->periode_fin)->format('d/m/Y') }}</div>
        <div class="box"><strong>Date</strong><br>{{ optional($reversement->date_reversement)->format('d/m/Y') ?? '—' }}</div>
    </section>

    <section class="totals">
        <div class="box">Impayés<span class="amount">{{ number_format($reversement->total_restant, 0, ',', ' ') }} F</span></div>
        <div class="box">Dépenses<span class="amount">{{ number_format($reversement->depenses_effectuees, 0, ',', ' ') }} F</span></div>
        <div class="box">Nouvelle caution<span class="amount">{{ number_format($reversement->nouvelle_caution, 0, ',', ' ') }} F</span></div>
        <div class="box">Caution SODECI/CIE<span class="amount">{{ number_format($reversement->cautionSodeci, 0, ',', ' ') }} F</span></div>
        <div class="box">Après commission<span class="amount">{{ number_format($reversement->montant_apres_commission, 0, ',', ' ') }} F</span></div>
        <div class="box">Net reversé<span class="amount">{{ number_format($reversement->net_a_reverser, 0, ',', ' ') }} F</span></div>
    </section>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Porte</th><th>Locataire</th><th class="right">Loyer</th><th class="right">Attendu</th><th class="right">Payé</th><th class="right">Impayé</th></tr></thead>
            <tbody>
            @forelse($reversement->details as $detail)
                <tr><td>{{ $detail->porte?->numero_porte ?? '—' }}</td><td>{{ $detail->locataire?->name ?? '—' }}</td><td class="right">{{ number_format($detail->montant_loyer, 0, ',', ' ') }}</td><td class="right">{{ number_format($detail->montant_attendu, 0, ',', ' ') }}</td><td class="right">{{ number_format($detail->total_paye, 0, ',', ' ') }}</td><td class="right">{{ number_format($detail->impayes, 0, ',', ' ') }}</td></tr>
            @empty
                <tr><td colspan="6">Aucun détail disponible.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</main>
<script>
    window.addEventListener('load', async () => {
        try { await screen.orientation.lock('landscape'); } catch (_) {}
    });
</script>
</body>
</html>
