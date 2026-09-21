<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #0F172A;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1C9F93;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #1C9F93;
            margin: 0;
            font-size: 20px;
        }

        .header p {
            color: #64748B;
            margin: 5px 0 0;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        td {
            padding: 8px 0;
            border-bottom: 1px solid #E2E8F0;
        }

        td.label {
            color: #64748B;
            width: 40%;
        }

        td.value {
            font-weight: bold;
        }

        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748B;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BON DE RÉCEPTION</h1>
        <p>N° {{ str_pad($bon->id, 6, '0', STR_PAD_LEFT) }} — Dima Groupe</p>
    </div>

    <table>
        <tr>
            <td class="label">Chantier</td>
            <td class="value">{{ $bon->demande->chantier->nomChantier }}</td>
        </tr>
        <tr>
            <td class="label">Désignation</td>
            <td class="value">{{ $bon->demande->designation }}</td>
        </tr>
        <tr>
            <td class="label">Quantité demandée</td>
            <td class="value">{{ $bon->demande->quantite_demandee }} {{ $bon->demande->unite }}</td>
        </tr>
        <tr>
            <td class="label">Quantité reçue (ce bon)</td>
            <td class="value">{{ $bon->quantite_recue }} {{ $bon->demande->unite }}</td>
        </tr>
        <tr>
            <td class="label">Date de réception</td>
            <td class="value">{{ \Carbon\Carbon::parse($bon->date_reception)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Réceptionné par</td>
            <td class="value">{{ $bon->receptionneePar->nom_complet }}</td>
        </tr>
        @if ($bon->observation)
            <tr>
                <td class="label">Observation</td>
                <td class="value">{{ $bon->observation }}</td>
            </tr>
        @endif
    </table>

    <div class="footer">
        <div>Signature réceptionnaire</div>
        <div>Cachet chantier</div>
    </div>
</body>

</html>
