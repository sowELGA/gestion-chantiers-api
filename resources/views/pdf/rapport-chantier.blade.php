<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $rapport->titre }}</title>
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
            line-height: 1.6;
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        .header {
            display: table;
            width: 100%;
            background: #0F172A;
            padding: 12px 18px;
            margin-bottom: 18px;
            border-radius: 4px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: middle;
        }

        .header-right {
            text-align: right;
        }

        .company {
            font-size: 16px;
            font-weight: bold;
            color: #1C9F93;
            letter-spacing: .5px;
        }

        .company-sub {
            font-size: 9px;
            color: #94A3B8;
            margin-top: 2px;
        }

        .doc-title {
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
        }

        .doc-num {
            font-size: 9px;
            color: #94A3B8;
        }

        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .type-avancement {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .type-incident {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .type-livraison {
            background: #D1FAE5;
            color: #065F46;
        }

        .type-reunion {
            background: #FEF3C7;
            color: #92400E;
        }

        .type-autre {
            background: #E2E8F0;
            color: #475569;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .info-table td {
            padding: 6px 0;
            border-bottom: 1px solid #E2E8F0;
            font-size: 10px;
        }

        .info-table td.label {
            color: #64748B;
            width: 30%;
        }

        .info-table td.value {
            font-weight: bold;
        }

        .contenu-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 16px;
            white-space: pre-wrap;
        }

        .footer {
            margin-top: 40px;
            font-size: 9px;
            color: #94A3B8;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="company">DIMA GROUPE</div>
            <div class="company-sub">Gestion & Suivi des Chantiers</div>
        </div>
        <div class="header-right">
            <div class="doc-title">Rapport de chantier</div>
            <div class="doc-num">N° {{ str_pad($rapport->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <span class="type-badge type-{{ $rapport->type }}">{{ $rapport->type_label }}</span>
    <h2 style="margin-bottom: 12px;">{{ $rapport->titre }}</h2>

    <table class="info-table">
        <tr>
            <td class="label">Chantier</td>
            <td class="value">{{ $rapport->chantier->nomChantier }}</td>
        </tr>
        <tr>
            <td class="label">Date du rapport</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($rapport->date_rapport)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
        </tr>
        <tr>
            <td class="label">Rédigé par</td>
            <td class="value">{{ $rapport->auteur->nom_complet }}</td>
        </tr>
    </table>

    <div class="contenu-box">{{ $rapport->contenu }}</div>

    <div class="footer">Document généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }} — DIMA GROUPE
    </div>
</body>

</html>
