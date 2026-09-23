<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    public function test_un_user_peut_changer_son_mot_de_passe(): void
    {
        $user = $this->creerUserAvecRole('admin', ['premiere_connexion' => true]);

        $response = $this->connecterEnTantQue($user)->postJson('/api/changer-mot-de-passe', [
            'password' => 'nouveauMotDePasse123',
            'password_confirmation' => 'nouveauMotDePasse123',
        ]);

        $response->assertOk();
        $this->assertFalse($user->fresh()->premiere_connexion);
    }

    public function test_le_changement_echoue_si_la_confirmation_ne_correspond_pas(): void
    {
        $user = $this->creerUserAvecRole('admin');

        $response = $this->connecterEnTantQue($user)->postJson('/api/changer-mot-de-passe', [
            'password' => 'nouveauMotDePasse123',
            'password_confirmation' => 'autrechose',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_le_changement_echoue_si_trop_court(): void
    {
        $user = $this->creerUserAvecRole('admin');

        $response = $this->connecterEnTantQue($user)->postJson('/api/changer-mot-de-passe', [
            'password' => 'court',
            'password_confirmation' => 'court',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }
}
