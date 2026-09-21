@component('emails.layout')
    <p style="margin:0 0 16px; font-size: 15px; color: #0F172A;">
        Bonjour,
    </p>

    <p style="margin:0 0 20px; font-size: 14px; color: #475569; line-height: 1.6;">
        Une nouvelle demande de réinitialisation de mot de passe vient d'être soumise.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px;">
        <tr>
            <td style="padding: 14px 18px; border-bottom: 1px solid #FECACA;">
                <p style="margin:0; font-size: 11px; color: #991B1B; text-transform: uppercase;">Utilisateur concerné</p>
                <p style="margin:2px 0 0; font-size: 14px; font-weight: bold; color: #0F172A;">
                    {{ $utilisateur?->nomComplet ?? $demande->email }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px 18px;">
                <p style="margin:0; font-size: 11px; color: #991B1B; text-transform: uppercase;">Email</p>
                <p style="margin:2px 0 0; font-size: 14px; color: #0F172A;">{{ $demande->email }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0; font-size: 13px; color: #64748B;">
        Merci de traiter cette demande depuis l'espace Administrateur.
    </p>

    @include('emails.partials.bouton')
@endcomponent
