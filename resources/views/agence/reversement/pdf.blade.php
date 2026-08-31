<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #17324d; font-size: 10px; }
        .watermark { position: fixed; top: 25%; left: 31%; width: 38%; opacity: .08; z-index: -1; }
        .brand-logo { max-width: 68px; max-height: 55px; margin-bottom: 6px; }
        h1 { margin: 0; color: #075a91; font-size: 21px; }
        .header { border-bottom: 2px solid #075a91; padding-bottom: 12px; margin-bottom: 16px; }
        .muted { color: #64748b; }
        .info, .totals { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info td { width: 33.33%; padding: 5px 8px; vertical-align: top; }
        .totals td { padding: 10px; border: 1px solid #dfe7ee; background: #f5f8fa; }
        .amount { color: #075a91; font-size: 14px; font-weight: bold; }
        table.details { width: 100%; border-collapse: collapse; }
        .details th { padding: 7px 5px; color: white; background: #075a91; text-align: left; }
        .details td { padding: 6px 5px; border-bottom: 1px solid #dfe7ee; }
        .right { text-align: right !important; }
        .approvals { width: 100%; margin-top: 18px; border-collapse: collapse; page-break-inside: avoid; }
        .approvals td { width: 33.33%; text-align: center; vertical-align: bottom; padding: 4px 12px; }
        .approval-image { max-width: 105px; max-height: 48px; display: block; margin: 0 auto 4px; }
        .approval-name { font-weight: bold; color: #17324d; }
        .approval-title { color: #64748b; font-size: 8px; }
        .stamp { max-width: 82px; max-height: 70px; }
        .footer { margin-top: 18px; text-align: right; color: #64748b; }
    </style>
</head>
<body>
    @if($documentLogo)
        <img class="watermark" src="{{ $documentLogo }}" alt="">
    @endif
    <div class="header">
        @if($documentLogo)
            <img class="brand-logo" src="{{ $documentLogo }}" alt="Logo">
        @endif
        <h1>Fiche de reversement</h1>
        <div class="muted">{{ $reversement->agence?->name ?? 'Agence immobilière' }}</div>
    </div>

    <table class="info">
        <tr>
            <td><strong>Lot</strong><br>{{ $reversement->lot?->name ?? '—' }} — Îlot {{ $reversement->lot?->num_ilot ?? '—' }} / Lot {{ $reversement->lot?->num_lot ?? '—' }}</td>
            <td><strong>Période</strong><br>{{ optional($reversement->periode_debut)->format('d/m/Y') }} au {{ optional($reversement->periode_fin)->format('d/m/Y') }}</td>
            @if($reversement->type_reversement === 'vente')
                <td><strong>Acheteur</strong><br>{{ $reversement->vente?->acheteur?->name ?? '—' }}<br>{{ $reversement->vente?->acheteur?->telephone1 ?? '' }}</td>
            @endif
        </tr>
        <tr>
            <td><strong>Date du reversement</strong><br>{{ optional($reversement->date_reversement)->format('d/m/Y') ?? '—' }}</td>
            <td><strong>Mode de paiement</strong><br>{{ $reversement->mode_paiement ?? '—' }}</td>
            <td><strong>Référence de paiement</strong><br>{{ $reversement->reference_paiement ?? '—' }}</td>
        </tr>
    </table>

    @if($reversement->type_reversement === 'vente')
    <table class="totals">
        <tr>
            <td>Prix du lot<br><span class="amount">{{ number_format($reversement->total_attendu, 0, ',', ' ') }} FCFA</span></td>
            <td>Versé sur la période<br><span class="amount">{{ number_format($reversement->total_encaisse, 0, ',', ' ') }} FCFA</span></td>
            <td>Commission agence ({{ number_format($reversement->taux_commission, 2, ',', ' ') }} %)<br><span class="amount">{{ number_format($reversement->montant_commission, 0, ',', ' ') }} FCFA</span></td>
            <td>Net reversé au propriétaire<br><span class="amount">{{ number_format($reversement->net_a_reverser, 0, ',', ' ') }} FCFA</span></td>
            <td>Reste à payer sur le lot<br><span class="amount">{{ number_format($reversement->total_restant, 0, ',', ' ') }} FCFA</span></td>
        </tr>
    </table>

    <table class="details">
        <thead><tr><th>Rang</th><th>Date du versement</th><th>N° reçu</th><th class="right">Montant versé</th><th class="right">Reste après versement</th></tr></thead>
        <tbody>
        @forelse($salePayments as $index => $payment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($payment['date'])->format('d/m/Y H:i') }}</td>
                <td>{{ $payment['receipt'] ?? '—' }}</td>
                <td class="right">{{ number_format($payment['amount'], 0, ',', ' ') }} FCFA</td>
                <td class="right">{{ number_format($payment['remaining'], 0, ',', ' ') }} FCFA</td>
            </tr>
        @empty
            <tr><td colspan="5">Aucun versement disponible.</td></tr>
        @endforelse
        </tbody>
    </table>
    @else
    <table class="totals">
        <tr>
            <td>Total attendu<br><span class="amount">{{ number_format($reversement->total_attendu, 0, ',', ' ') }} FCFA</span></td>
            <td>Total encaissé<br><span class="amount">{{ number_format($reversement->total_encaisse, 0, ',', ' ') }} FCFA</span></td>
            <td>Impayés<br><span class="amount">{{ number_format($reversement->total_restant, 0, ',', ' ') }} FCFA</span></td>
            <td>Dépenses<br><span class="amount">{{ number_format($reversement->depenses_effectuees, 0, ',', ' ') }} FCFA</span></td>
            <td>Frais de dossier non reversés<br><span class="amount">{{ number_format($reversement->frais_dossier, 0, ',', ' ') }} FCFA</span></td>
            <td>Maintenances — montant versé sur la période<br><span class="amount">{{ number_format($reversement->montant_maintenances, 0, ',', ' ') }} FCFA</span></td>
            <td>Nouvelle caution<br><span class="amount">{{ number_format($reversement->nouvelle_caution, 0, ',', ' ') }} FCFA</span></td>
            <td>Caution SODECI/CIE<br><span class="amount">{{ number_format($reversement->cautionSodeci, 0, ',', ' ') }} FCFA</span></td>
            <td>Après commission<br><span class="amount">{{ number_format($reversement->montant_apres_commission, 0, ',', ' ') }} FCFA</span></td>
            <td>Net reversé<br><span class="amount">{{ number_format($reversement->net_a_reverser, 0, ',', ' ') }} FCFA</span></td>
        </tr>
    </table>

    <table class="details">
        <thead><tr><th>Porte</th><th>Locataire</th><th class="right">Loyer</th><th class="right">Attendu</th><th class="right">Payé</th><th class="right">Impayé</th></tr></thead>
        <tbody>
        @forelse($reversement->details as $detail)
            <tr>
                <td>{{ $detail->porte?->numero_porte ?? '—' }}</td>
                <td>{{ $detail->locataire?->name ?? '—' }}</td>
                <td class="right">{{ number_format($detail->montant_loyer, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($detail->montant_attendu, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($detail->total_paye, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($detail->impayes, 0, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Aucun détail disponible.</td></tr>
        @endforelse
        </tbody>
    </table>
    @endif

    @if(!empty($documentApproval['signatures']) || !empty($documentApproval['cachet']))
        <table class="approvals">
            <tr>
                @foreach($documentApproval['signatures'] as $signature)
                    <td>
                        @if($signature['image'])
                            <img class="approval-image" src="{{ $signature['image'] }}" alt="">
                        @endif
                        <div class="approval-name">{{ $signature['name'] ?: $signature['title'] }}</div>
                        <div class="approval-title">{{ $signature['title'] }}</div>
                    </td>
                @endforeach
                @if($documentApproval['cachet'])
                    <td><img class="stamp" src="{{ $documentApproval['cachet'] }}" alt="Cachet de l'agence"></td>
                @endif
            </tr>
        </table>
    @endif

    <div class="footer">Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
