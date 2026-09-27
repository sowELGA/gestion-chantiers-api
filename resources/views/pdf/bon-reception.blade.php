<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Bon de réception N°{{ $bon->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #0F172A;
        }

        .header {
            display: table;
            width: 100%;
            background: #0F172A;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .company {
            font-size: 18px;
            font-weight: bold;
            color: #1C9F93;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
        }

        .doc-num {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }

        .info-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 12px 16px;
        }

        .info-title {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            width: 45%;
            font-size: 10px;
            color: #64748B;
        }

        .info-value {
            display: table-cell;
            font-size: 10px;
            font-weight: bold;
            color: #0F172A;
        }

        .section-title {
            background: #1C9F93;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 6px 12px;
            border-radius: 4px 4px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #F1F5F9;
            color: #64748B;
            font-size: 9px;
            text-transform: uppercase;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #E2E8F0;
        }

        tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #F1F5F9;
            font-size: 11px;
        }

        .highlight {
            background: #E8F5F4;
        }

        .text-green {
            color: #1C9F93;
            font-weight: bold;
        }

        .text-orange {
            color: #f59e0b;
            font-weight: bold;
        }

        .signature-zone {
            display: table;
            width: 100%;
            margin-top: 30px;
        }

        .sig-cell {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
        }

        .sig-box {
            border-top: 1px solid #CBD5E1;
            padding-top: 8px;
            text-align: center;
        }

        .sig-label {
            font-size: 9px;
            color: #64748B;
            text-transform: uppercase;
        }

        .sig-line {
            height: 40px;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #E2E8F0;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <div class="company">DIMA GROUPE</div>
            <div style="font-size:9px; color:#64748B; margin-top:2px;">Sotrac Mermoz, Lot N°71 — Dakar</div>
        </div>
        <div class="header-right">
            <div class="doc-title">BON DE RECEPTION</div>
            <div class="doc-num">N° BR-{{ str_pad($bon->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Chantier</div>
                <div class="info-row">
                    <span class="info-label">Nom :</span>
                    <span class="info-value">{{ $bon->demande->chantier->nomChantier }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Localisation :</span>
                    <span class="info-value">{{ $bon->demande->chantier->localisation }}</span>
                </div>
            </div>
        </div>
        <div class="info-col">
            <div class="info-box">
                <div class="info-title">Réception</div>
                <div class="info-row">
                    <span class="info-label">Date :</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($bon->date_reception)->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Réceptionné par :</span>
                    <span class="info-value">{{ $bon->receptionneePar->nom_complet }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Généré le :</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    @php
        $totalCumul = $bon->demande->bonReceptions->sum('quantite_recue');
        $quantiteRestante = max(0, $bon->demande->quantite_demandee - $totalCumul);
    @endphp
    <div class="section-title">Détails de la réception</div>
    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Qté commandée</th>
                <th>Qté reçue (cumul)</th>
                <th>Qté reçue (ce bon)</th>
                <th>Qté restante</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <tr class="highlight">
                <td><strong>{{ $bon->demande->designation }}</strong></td>
                <td>{{ $bon->demande->quantite_demandee }} {{ $bon->demande->unite }}</td>
                <td>{{ $totalCumul }} {{ $bon->demande->unite }}</td>
                <td class="text-green">{{ $bon->quantite_recue }} {{ $bon->demande->unite }}</td>
                <td class="{{ $quantiteRestante > 0 ? 'text-orange' : 'text-green' }}">{{ $quantiteRestante }}
                    {{ $bon->demande->unite }}</td>
                <td>
                    @if ($quantiteRestante <= 0)
                        <span style="color:#1C9F93; font-weight:bold;">✓ Complet</span>
                    @else
                        <span style="color:#f59e0b; font-weight:bold;">Partiel</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    @if ($bon->observation)
        <div
            style="margin-top:12px; padding:10px 12px; background:#FFF7ED; border:1px solid #FED7AA; border-radius:4px;">
            <strong style="font-size:10px; color:#92400e;">Observation :</strong>
            <span style="font-size:10px; color:#78350f;">{{ $bon->observation }}</span>
        </div>
    @endif

    <div class="signature-zone">
        <div class="sig-cell">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Le Pointeur</div>
                <div style="font-size:10px; font-weight:bold; color:#0F172A; margin-top:4px;">
                    {{ $bon->receptionneePar->nom_complet }}</div>
            </div>
        </div>
        <div class="sig-cell">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">La Direction</div>
                <div style="font-size:10px; font-weight:bold; color:#0F172A; margin-top:4px;">Dima Groupe</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Bon de réception N° BR-{{ str_pad($bon->id, 4, '0', STR_PAD_LEFT) }}
        — Dima Groupe © {{ date('Y') }} — Document officiel
    </div>

</body>

</html>
