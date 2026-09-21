<?php

namespace App\Mail;

use App\Models\DemandeResetMdp;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemandeResetNotifMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ?User $utilisateurDemandeur,
        public DemandeResetMdp $demande
    ) {}

    public function build()
    {
        return $this->subject('Nouvelle demande de réinitialisation de mot de passe')
            ->view('emails.demande-reset-notif')
            ->with([
                'utilisateur' => $this->utilisateurDemandeur,
                'demande' => $this->demande,
            ]);
    }
}
