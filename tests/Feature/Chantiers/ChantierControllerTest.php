<?php

namespace Tests\Feature\Chantiers;

use App\Models\Chantier;
use Tests\TestCase;

class ChantierControllerTest extends TestCase
{
    public function test_un_directeur_des_travaux_peut_creer_un_chantier(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');

        $response = $this->connecterEnTantQue($dt)->postJson('/api/chantiers', [
            'nomChantier' => 'Résidence Les Almadies',
            'localisation' => 'Dakar',
            'budget_prevu' => 50000000,
            'date_debut' => now()->toDateString(),
            'date_fin_prevue' => now()->addMonths(6)->toDateString(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('chantiers', ['nomChantier' => 'Résidence Les Almadies', 'statut' => 'en_attente']);
    }

    public function test_un_chef_de_projet_ne_peut_pas_creer_un_chantier(): void
    {
        $chefProjet = $this->creerUserAvecRole('chef_projet');

        $response = $this->connecterEnTantQue($chefProjet)->postJson('/api/chantiers', [
            'nomChantier' => 'Test',
            'localisation' => 'Dakar',
            'date_debut' => now()->toDateString(),
            'date_fin_prevue' => now()->addMonths(3)->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_la_date_de_fin_doit_etre_apres_la_date_de_debut(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');

        $response = $this->connecterEnTantQue($dt)->postJson('/api/chantiers', [
            'nomChantier' => 'Test',
            'localisation' => 'Dakar',
            'date_debut' => now()->toDateString(),
            'date_fin_prevue' => now()->subDay()->toDateString(),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('date_fin_prevue');
    }

    public function test_affecter_un_chef_de_projet_valide(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');
        $chefProjet = $this->creerUserAvecRole('chef_projet');
        $chantier = Chantier::factory()->create();

        $response = $this->connecterEnTantQue($dt)->patchJson("/api/chantiers/{$chantier->id}/chef-projet", [
            'chef_projet_id' => $chefProjet->id,
        ]);

        $response->assertOk();
        $this->assertEquals($chefProjet->id, $chantier->fresh()->chef_projet_id);
    }

    public function test_affecter_un_user_sans_role_chef_projet_est_refuse(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');
        $adminSeul = $this->creerUserAvecRole('admin');
        $chantier = Chantier::factory()->create();

        $response = $this->connecterEnTantQue($dt)->patchJson("/api/chantiers/{$chantier->id}/chef-projet", [
            'chef_projet_id' => $adminSeul->id,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('chef_projet_id');
    }

    public function test_un_pointeur_deja_affecte_ailleurs_ne_peut_pas_etre_reaffecte(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');
        $pointeur = $this->creerUserAvecRole('pointeur');
        Chantier::factory()->create(['pointeur_id' => $pointeur->id]);
        $autreChantier = Chantier::factory()->create();

        $response = $this->connecterEnTantQue($dt)->patchJson("/api/chantiers/{$autreChantier->id}/pointeur", [
            'pointeur_id' => $pointeur->id,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('pointeur_id');
    }

    public function test_changer_le_statut_suit_les_transitions_autorisees(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');
        $chantier = Chantier::factory()->create(['statut' => 'en_attente']);

        $response = $this->connecterEnTantQue($dt)->patchJson("/api/chantiers/{$chantier->id}/statut/en_cours");

        $response->assertOk();
        $this->assertEquals('en_cours', $chantier->fresh()->statut);
    }

    public function test_une_transition_de_statut_invalide_est_refusee(): void
    {
        $dt = $this->creerUserAvecRole('directeur_travaux');
        $chantier = Chantier::factory()->create(['statut' => 'en_attente']);

        // en_attente → livre n'est pas une transition valide (doit passer par en_cours)
        $response = $this->connecterEnTantQue($dt)->patchJson("/api/chantiers/{$chantier->id}/statut/livre");

        $response->assertStatus(422);
        $this->assertEquals('en_attente', $chantier->fresh()->statut);
    }
}
