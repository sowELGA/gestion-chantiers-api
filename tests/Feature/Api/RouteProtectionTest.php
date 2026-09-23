<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    public function test_un_admin_peut_acceder_a_la_gestion_des_utilisateurs(): void
    {
        $admin = $this->creerUserAvecRole('admin');

        $response = $this->connecterEnTantQue($admin)->getJson('/api/admin/utilisateurs');

        $response->assertOk();
    }

    public function test_un_non_admin_ne_peut_pas_acceder_a_la_gestion_des_utilisateurs(): void
    {
        $chefProjet = $this->creerUserAvecRole('chef_projet');

        $response = $this->connecterEnTantQue($chefProjet)->getJson('/api/admin/utilisateurs');

        $response->assertStatus(403);
    }

    public function test_un_compte_desactive_est_rejete_meme_avec_un_token_valide(): void
    {
        $admin = $this->creerUserAvecRole('admin', ['actif' => false]);

        $response = $this->connecterEnTantQue($admin)->getJson('/api/admin/utilisateurs');

        $response->assertStatus(403);
    }

    public function test_un_daf_sans_capacite_approvisionnements_est_rejete(): void
    {
        $daf = User::factory()->create();
        $roleDaf = \App\Models\Role::where('nom', 'daf')->first();
        $daf->roles()->attach($roleDaf->id, ['gere_approvisionnements' => false, 'gere_depenses' => true]);

        $response = $this->connecterEnTantQue($daf->fresh('roles'))->getJson('/api/daf/approvisionnements');

        $response->assertStatus(403);
    }
}
