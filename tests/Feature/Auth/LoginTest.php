<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_un_utilisateur_peut_se_connecter_avec_de_bons_identifiants(): void
    {
        $user = $this->creerUserAvecRole('admin', ['email' => 'admin@test.com', 'password' => bcrypt('motdepasse123')]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'motdepasse123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'roles']]);

        $this->assertEquals('admin', $response->json('user.roles.0.nom'));
    }

    public function test_le_login_echoue_avec_un_mauvais_mot_de_passe(): void
    {
        $this->creerUserAvecRole('admin', ['email' => 'admin@test.com', 'password' => bcrypt('bonmotdepasse')]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'mauvais_mot_de_passe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $this->creerUserAvecRole('admin', [
            'email' => 'inactif@test.com',
            'password' => bcrypt('motdepasse123'),
            'actif' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactif@test.com',
            'password' => 'motdepasse123',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('désactivé', $response->json('message'));
    }

    public function test_une_route_protegee_renvoie_401_sans_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_renvoie_les_infos_du_user_connecte_avec_ses_roles(): void
    {
        $user = $this->creerUserAvecRole(['admin', 'directeur_travaux']);

        $response = $this->connecterEnTantQue($user)->getJson('/api/me');

        $response->assertOk();
        $noms = collect($response->json('data.roles'))->pluck('nom')->all();
        $this->assertEqualsCanonicalizing(['admin', 'directeur_travaux'], $noms);
    }
}
