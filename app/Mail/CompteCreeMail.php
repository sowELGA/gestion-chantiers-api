<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompteCreeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $motDePasseTemporaire
    ) {}

    public function build()
    {
        return $this->subject('Votre compte a été créé')
            ->view('emails.compte-cree')
            ->with([
                'user' => $this->user,
                'motDePasse' => $this->motDePasseTemporaire,
            ]);
    }
}
