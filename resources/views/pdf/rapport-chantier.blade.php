<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $rapport->titre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #0F172A; line-height: 1.5; }
        @page { size: A4 portrait; margin: 15mm 15mm 15mm 15mm; }
        .header { display: table; width: 100%; background: #0F172A; padding: 10px 16px; margin-bottom: 14px; border-radius: 4px; }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .company { font-size: 15px; font-weight: bold; color: #1C9F93; letter-spacing: 0.5px; }
        .company-sub { font-size: 8px; color: #94A3B8; margin-top: 2px; }
        .doc-title { font-size: 11px; font-weight: bold; color: #FFFFFF; text-transform: uppercase; }
        .doc-num { font-size: 8px; color: #64748B; }
        .type-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .type-avancement { background: #DBEAFE; color: #1D4ED8; }
        .type-incident { background: #FEE2E2; color: #DC2626; }
        .type-livraison { background: #FEF3C7; color: #B45309; }
        .type-reunion { background: #EDE9FE; color: #6D28D9; }
        .type-autre { background: #F1F5F9; color: #475569; }
        .titre-rapport { font-size: 18px; font-weight: bold; color: #0F172A; margin-bottom: 10px; }
        .info-bar { display: table; width: 100%; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 4px; padding: 10px 14px; margin-bottom: 18px; }
        .info-cell { display: table-cell; padding-right: 12px; vertical-align: top; }
        .info-label { font-size: 7.5px; color: #64748B; text-transform: uppercase; font-weight: 600; margin-bottom: 2px; }
        .info-value { font-size: 10px; font-weight: bold; color: #0F172A; }
        .contenu-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #64748B; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #E2E8F0; }
        .contenu { font-size: 10.5px; color: #334155; white-space: pre-wrap; line-height: 1.7; }
        .footer { text-align: center; font-size: 8px; color: #94A3B8; margin-top: 24px; padding-top: 8px; border-top: 0.5px solid #E2E8F0; }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <div class="company">DIMA GROUPE</div>
            <div class="company-sub">Sotrac Mermoz, Lot N°71 — Dakar, Sénégal</div>
        </div>
        <div class="header-right">
            <div class="doc-title">RAPPORT DE CHANTIER</div>
            <div class="doc-num">Document interne — RC-{{ str_pad($rapport->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <span class="type-badge type-{{ $rapport->type }}">{{ $rapport->type_label }}</span>
    <div class="titre-rapport">{{ $rapport->titre }}</div>

    <div class="info-bar">
        <div class="info-cell">
            <div class="info-label">Chantier</div>
            <div class="info-value">{{ $rapport->chantier->nomChantier }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Date du rapport</div>
            <div class="info-value">{{ $rapport->date_rapport->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Rédigé par</div>
            <div class="info-value">{{ $rapport->auteur->prenomUser }} {{ $rapport->auteur->nomUser }}</div>
        </div>
        <div class="info-cell" style="padding-right: 0;">
            <div class="info-label">Édité le</div>
            <div class="info-value">{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="contenu-title">Contenu du rapport</div>
    <div class="contenu">{{ $rapport->contenu }}</div>

    <div class="footer">
        © {{ date('Y') }} Dima Groupe — Rapport créé le
        {{ $rapport->created_at->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}
        — Document généré via le système de gestion de chantier
    </div>

</body>
</html>