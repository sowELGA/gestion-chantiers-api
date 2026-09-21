@component('emails.layout')
    <p style="margin:0 0 16px; font-size: 15px; color: #0F172A;">
        Bonjour <strong>{{ $user->prenomUser }} {{ $user->nomUser }}</strong>,
    </p>

    <p style="margin:0 0 20px; font-size: 14px; color: #475569; line-height: 1.6;">
        Votre compte vient d'être créé sur la plateforme de gestion et suivi des chantiers de Dima Groupe. Voici vos
        identifiants de connexion :
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; margin-bottom: 8px;">
        <tr>
            <td style="padding: 14px 18px; border-bottom: 1px solid #E2E8F0;">
                <p style="margin:0; font-size: 11px; color: #94A3B8; text-transform: uppercase;">Email</p>
                <p style="margin:2px 0 0; font-size: 14px; font-weight: bold; color: #0F172A;">{{ $user->email }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px 18px;">
                <p style="margin:0; font-size: 11px; color: #94A3B8; text-transform: uppercase;">Mot de passe temporaire</p>
                <p style="margin:2px 0 0; font-size: 16px; font-weight: bold; color: #1C9F93; font-family: monospace;">
                    {{ $motDePasse }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0; font-size: 13px; color: #64748B;">
        ⚠️ Pour des raisons de sécurité, vous devrez définir un nouveau mot de passe dès votre première connexion.
    </p>

    @include('emails.partials.bouton')

    <p style="margin:0; font-size: 12px; color: #94A3B8;">
        Si vous n'êtes pas à l'origine de cette demande, contactez immédiatement votre administrateur.
    </p>
@endcomponent
