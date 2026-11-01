<?php

namespace Tests\Feature\Planning;

use App\Models\Chantier;
use App\Models\Phase;
use Tests\TestCase;

class PlanningControllerTest extends TestCase
{
    public function test_un_chef_de_projet_peut_creer_une_phase_sur_son_chantier(): void
    {
        $chef = $this->creerUserAvecRole('chef_projet');
        $chantier = Chantier::factory()->enCours()->create(['chef_projet_id' => $chef->id]);

        $response = $this->connecterEnTantQue($chef)->postJson("/api/mes-chantiers/{$chantier->id}/phases", [
            'nomPhase' => 'Fondations',
            'ordre' => 1,
            'typePhase' => 'gros_oeuvre',
            'date_debut' => $chantier->date_debut->toDateString(),
            'date_fin_prevue' => $chantier->date_fin_prevue->toDateString(),
        ]);

        $response->assertCreated();
    }

    public function test_un_chef_de_projet_ne_peut_pas_toucher_au_chantier_dun_autre(): void
    {
        $chef = $this->creerUserAvecRole('chef_projet');
        $autreChef = $this->creerUserAvecRole('chef_projet');
        $chantier = Chantier::factory()->enCours()->create(['chef_projet_id' => $autreChef->id]);

        $response = $this->connecterEnTantQue($chef)->getJson("/api/mes-chantiers/{$chantier->id}/phases");

        $response->assertStatus(403);
    }

    public function test_impossible_de_creer_une_phase_sur_un_chantier_livre(): void
    {
        $chef = $this->creerUserAvecRole('chef_projet');
        $chantier = Chantier::factory()->create(['chef_projet_id' => $chef->id, 'statut' => 'livre']);

        $response = $this->connecterEnTantQue($chef)->postJson("/api/mes-chantiers/{$chantier->id}/phases", [
            'nomPhase' => 'Test',
            'ordre' => 1,
            'typePhase' => 'gros_oeuvre',
            'date_debut' => now()->toDateString(),
            'date_fin_prevue' => now()->addMonth()->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_le_filtre_actives_exclut_les_phases_terminees(): void
    {
        $chef = $this->creerUserAvecRole('chef_projet');
        $chantier = Chantier::factory()->enCours()->create(['chef_projet_id' => $chef->id]);
        Phase::factory()->for($chantier)->create(['nomPhase' => 'Active', 'statutPhase' => 'en_cours']);
        Phase::factory()->for($chantier)->create(['nomPhase' => 'Finie', 'statutPhase' => 'terminee']);

        $response = $this->connecterEnTantQue($chef)->getJson("/api/mes-chantiers/{$chantier->id}/phases");

        $response->assertOk();
        $noms = collect($response->json('phases'))->pluck('nomPhase')->all();
        $this->assertContains('Active', $noms);
        $this->assertNotContains('Finie', $noms);
    }
}
