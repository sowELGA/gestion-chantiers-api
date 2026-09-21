<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Fiche de paie — Semaine {{ $semaine }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #0F172A;
            line-height: 1.2;
        }

        @page {
            size: A4 landscape;
            margin: 6mm 8mm 8mm 8mm;
        }

        /* ── HEADER ── */
        .header {
            display: table;
            width: 100%;
            background: #0F172A;
            padding: 6px 10px;
            margin-bottom: 6px;
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
            font-size: 12px;
            font-weight: bold;
            color: #1C9F93;
            letter-spacing: 0.5px;
        }

        .company-sub {
            font-size: 7px;
            color: #94A3B8;
            margin-top: 1px;
        }

        .doc-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #FFFFFF;
            text-transform: uppercase;
        }

        .doc-num {
            font-size: 7px;
            color: #64748B;
        }

        /* ── INFO BAR ── */
        .info-bar {
            display: table;
            width: 100%;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 3px;
            padding: 4px 8px;
            margin-bottom: 6px;
        }

        .info-cell {
            display: table-cell;
            padding-right: 8px;
            vertical-align: middle;
        }

        .info-label {
            font-size: 6.5px;
            color: #64748B;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-value {
            font-size: 8px;
            font-weight: bold;
            color: #0F172A;
        }

        /* ── SECTION TITLE ── */
        .section-title {
            background: #1C9F93;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 7.5px;
            padding: 2.5px 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 6px;
            border-radius: 2px 2px 0 0;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        /* ── TABLE ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        thead th {
            background: #F1F5F9;
            color: #475569;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2.5px 3px;
            text-align: center;
            border-bottom: 1px solid #CBD5E1;
            border-top: 1px solid #CBD5E1;
        }

        thead th.th-left {
            text-align: left;
            padding-left: 5px;
        }

        tbody td {
            padding: 2.5px 3px;
            border-bottom: 1px solid #F1F5F9;
            text-align: center;
            color: #334155;
            font-size: 7.5px;
        }

        tbody td.td-left {
            text-align: left;
            font-weight: 600;
            color: #0F172A;
            padding-left: 5px;
        }

        tbody tr:nth-child(even) {
            background: #FAFAFA;
        }

        /* Badges Statut */
        .badge-p {
            color: #0F766E;
            font-weight: bold;
        }

        .badge-a {
            color: #94A3B8;
        }

        .badge-m {
            color: #D97706;
            font-weight: bold;
        }

        .badge-hs {
            font-size: 5.5px;
            color: #B45309;
            font-weight: bold;
            display: block;
            margin-top: -1px;
        }

        /* ── SOUS-TOTAL ── */
        .subtotal-row td {
            background: #F0FDF4;
            color: #166534;
            font-weight: bold;
            font-size: 7.5px;
            padding: 3px 5px;
            border-top: 1px solid #BBF7D0;
            border-bottom: 1px solid #BBF7D0;
        }

        /* ── TOTAL GÉNÉRAL ── */
        .total-general-container {
            page-break-inside: avoid;
            margin-top: 8px;
        }

        .total-general {
            display: table;
            width: 100%;
            background: #0F172A;
            border-radius: 3px;
            padding: 6px 10px;
        }

        .total-label {
            display: table-cell;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8.5px;
            vertical-align: middle;
        }

        .total-amount {
            display: table-cell;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            color: #1C9F93;
            vertical-align: middle;
        }

        .total-cur {
            font-size: 7px;
            color: #94A3B8;
            font-weight: normal;
        }

        /* ── LÉGENDE ── */
        .legende {
            font-size: 6.5px;
            color: #64748B;
            margin-top: 4px;
            page-break-inside: avoid;
        }

        /* ── SIGNATURES ── */
        .sig-zone {
            display: table;
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .sig-cell {
            display: table-cell;
            width: 33.33%;
            padding: 0 5px;
        }

        .sig-box {
            border-top: 1px solid #94A3B8;
            padding-top: 3px;
            text-align: center;
        }

        .sig-line {
            height: 18px;
        }

        .sig-lbl {
            font-size: 6.5px;
            color: #64748B;
            text-transform: uppercase;
            font-weight: bold;
        }

        .sig-name {
            font-size: 7.5px;
            font-weight: bold;
            color: #0F172A;
            margin-top: 1px;
        }

        /* ── FOOTER ── */
        .footer {
            text-align: center;
            font-size: 6.5px;
            color: #94A3B8;
            margin-top: 6px;
            padding-top: 3px;
            border-top: 0.5px solid #E2E8F0;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            <div class="company">DIMA GROUPE</div>
            <div class="company-sub">Sotrac Mermoz, Lot N°71 — Dakar, Sénégal</div>
        </div>
        <div class="header-right">
            <div class="doc-title">FICHE DE PAIE HEBDOMADAIRE</div>
            <div class="doc-num">Document officiel — Confidentiel</div>
        </div>
    </div>

    {{-- INFO CHANTIER --}}
    <div class="info-bar">
        <div class="info-cell">
            <div class="info-label">Chantier</div>
            <div class="info-value">{{ $chantier->nomChantier }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Adresse</div>
            <div class="info-value">{{ $chantier->localisation }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Semaine</div>
            <div class="info-value">N° {{ $semaine }} / {{ $annee }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Période</div>
            <div class="info-value">Du {{ $debutSemaine }} au {{ $finSemaine }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Effectif</div>
            <div class="info-value">{{ $groupes->flatten()->count() }} ouvrier(s)</div>
        </div>
        <div class="info-cell" style="padding-right:0;">
            <div class="info-label">Édité le</div>
            <div class="info-value">{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    @php
        // $samedi vient du contrôleur (SemaineHelper::debutDepuisNumero) :
        // c'est la même date de référence utilisée pour filtrer les
// pointages. On ne la recalcule PAS ici pour éviter tout risque de
// décalage avec le reste de l'application.
        $joursDates = collect(range(0, 6))->map(fn($i) => $samedi->copy()->addDays($i));

        // NB : 'maladie' n'existe pas dans l'enum statutPointage
        // (present|absent uniquement) — conservé pour compatibilité future
        // si ce statut est ajouté un jour, mais ne peut pas se produire
        // actuellement.
        $statutMap = [
            'present' => 'P',
            'absent' => 'A',
            'maladie' => 'M',
        ];
    @endphp

    {{-- GROUPEMENT PAR MÉTIER REGROUPÉ (Ex: Maçon) --}}
    @foreach ($groupes as $groupeLibelle => $lignes)
        <div class="section-title">
            {{ $groupeLibelle }} ({{ $lignes->count() }})
        </div>

        <table>
            <thead>
                <tr>
                    <th class="th-left" style="width: 15%;">Ouvrier</th>
                    <th class="th-left" style="width: 12%;">Poste</th>
                    @foreach ($joursDates as $j)
                        <th style="width: 5%;">
                            {{ $j->locale('fr')->isoFormat('dd') }}<br>
                            <span
                                style="font-size:5.5px; font-weight:normal; color:#94A3B8;">{{ $j->format('d/m') }}</span>
                        </th>
                    @endforeach
                    <th style="width: 4%;">J.P</th>
                    <th style="width: 4%;">H.S</th>
                    <th style="width: 10%;">Sal. Base</th>
                    <th style="width: 8%;">Sal. H.S</th>
                    <th style="width: 12%; text-align: right; padding-right: 5px;">TOTAL (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lignes as $recap)
                    @php
                        // Détail jour par jour déjà chargé côté contrôleur
                        // (PdfService::genererFichePaie) — aucune requête
                        // Pointage ici, dans la boucle.
                        $pointagesOuvrier = $recap->pointagesParJour;
                    @endphp
                    <tr>
                        <td class="td-left">{{ $recap->ouvrier->nomComplet }}</td>

                        {{-- Poste ENREGISTRÉ LORS DU POINTAGE (jamais le
                             poste actuel de l'ouvrier, qui a pu changer
                             depuis). Attaché dynamiquement dans PdfService. --}}
                        <td class="td-left" style="color: #475569; font-weight: normal;">
                            {{ $recap->poste?->libelle ?? '—' }}
                        </td>

                        {{-- 7 Jours de pointage --}}
                        @foreach ($joursDates as $jourDate)
                            @php
                                $p = $pointagesOuvrier->get($jourDate->toDateString());
                                $s = $p ? $statutMap[$p->statutPointage] ?? '—' : '—';
                                $hSup = $p?->heures_sup ?? 0;
                                $badgeClass = match ($s) {
                                    'P' => 'badge-p',
                                    'M' => 'badge-m',
                                    default => 'badge-a',
                                };
                            @endphp
                            <td>
                                <span class="{{ $badgeClass }}">{{ $s }}</span>
                                @if ($hSup > 0)
                                    <span class="badge-hs">+{{ $hSup }}h</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Totaux individuels --}}
                        <td style="font-weight: bold; color: #0F172A;">{{ $recap->jours_presents }}</td>
                        <td style="color: #B45309; font-weight: bold;">
                            {{ $recap->total_heures_sup > 0 ? $recap->total_heures_sup . 'h' : '—' }}
                        </td>
                        <td>{{ number_format($recap->salaire_base, 0, ',', ' ') }}</td>
                        <td>{{ $recap->salaire_heures_sup > 0 ? number_format($recap->salaire_heures_sup, 0, ',', ' ') : '—' }}
                        </td>
                        <td style="text-align: right; font-weight: bold; color: #0F172A; padding-right: 5px;">
                            {{ number_format($recap->salaire_total, 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach

                {{-- Sous-total du groupe --}}
                <tr class="subtotal-row">
                    <td colspan="13" style="text-align: right; font-weight: bold;">
                        Sous-total {{ $groupeLibelle }} :
                    </td>
                    <td style="text-align: right; font-weight: bold; padding-right: 5px;">
                        {{ number_format($lignes->sum('salaire_total'), 0, ',', ' ') }} F
                    </td>
                </tr>
            </tbody>
        </table>
    @endforeach

    {{-- TOTAL GÉNÉRAL --}}
    <div class="total-general-container">
        <div class="total-general">
            <div class="total-label">
                TOTAL GÉNÉRAL À PAYER — S{{ $semaine }}/{{ $annee }} ({{ $groupes->flatten()->count() }}
                ouvriers)
            </div>
            <div class="total-amount">
                {{ number_format($totalGeneral, 0, ',', ' ') }}
                <span class="total-cur">FCFA</span>
            </div>
        </div>
    </div>

    {{-- LÉGENDE --}}
    <div class="legende">
        <strong>Légende :</strong>
        <span class="badge-p">P</span> = Présent &nbsp;·&nbsp;
        <span class="badge-a">A</span> = Absent &nbsp;·&nbsp;
        <span class="badge-m">M</span> = Maladie &nbsp;·&nbsp;
        <strong>J.P</strong> = Jours présents &nbsp;·&nbsp;
        <strong>H.S</strong> = Heures supplémentaires
    </div>

    {{-- SIGNATURES --}}
    <div class="sig-zone">
        <div class="sig-cell">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-lbl">Le Pointeur</div>
                <div class="sig-name">
                    {{ optional($groupes->flatten()->first()?->soumisParUser)->nomComplet ?? '—' }}
                </div>
            </div>
        </div>
        <div class="sig-cell">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-lbl">Le Chef de projet</div>
                <div class="sig-name">
                    {{ optional($groupes->flatten()->first()?->valideParUser)->nomComplet ?? '—' }}
                </div>
            </div>
        </div>
        <div class="sig-cell">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-lbl">Responsable RH</div>
                <div class="sig-name">DIMA GROUPE</div>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        © {{ date('Y') }} Dima Groupe — Document confidentiel de paie — Généré via le système de gestion de
        chantier
    </div>

</body>

</html>
