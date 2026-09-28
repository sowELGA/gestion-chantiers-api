<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MdpReinitialiseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $nouveauMotDePasse
    ) {}

    public function build()
    {
        return $this->subject('Votre mot de passe a été réinitialisé')
            ->view('emails.mdp-reinitialise')
            ->with([
                'user' => $this->user,
                'motDePasse' => $this->nouveauMotDePasse,
            ]);
    }
}
