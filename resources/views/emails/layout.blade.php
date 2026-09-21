<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dima Groupe</title>
</head>

<body style="margin:0; padding:0; background-color:#F8FAFC; font-family: Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color:#F8FAFC; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width: 480px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">

                    {{-- En-tête --}}
                    <tr>
                        <td style="background-color: #0F3D37; padding: 28px 32px;">
                            <p
                                style="margin:0; font-size: 20px; font-weight: bold; color: #1C9F93; letter-spacing: 0.5px;">
                                DIMA GROUPE</p>
                            <p
                                style="margin:4px 0 0; font-size: 11px; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px;">
                                Gestion &amp; Suivi des Chantiers</p>
                        </td>
                    </tr>

                    {{-- Contenu --}}
                    <tr>
                        <td style="padding: 32px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Pied de page --}}
                    <tr>
                        <td style="padding: 20px 32px; background-color: #F8FAFC; border-top: 1px solid #E2E8F0;">
                            <p style="margin:0; font-size: 11px; color: #94A3B8; text-align: center;">
                                © {{ date('Y') }} Dima Groupe — Tous droits réservés
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
