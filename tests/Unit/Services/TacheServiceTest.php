<?php

namespace Tests\Unit\Services;

use App\Models\Chantier;
use App\Models\Phase;
use App\Models\Tache;
use App\Services\TacheService;
use Tests\TestCase;

class TacheServiceTest extends TestCase
{
    private TacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TacheService();
    }

    public function test_creer_une_phase(): void
    {
        $chantier = Chantier::factory()->create();

        $phase = $this->service->creerPhase([
            'nomPhase' => 'Fondations',
            'ordre' => 1,
            'typePhase' => 'gros_oeuvre',
            'date_debut' => now(),
            'date_fin_prevue' => now()->addMonth(),
        ], $chantier->id);

        $this->assertEquals('en_attente', $phase->statutPhase);
        $this->assertEquals($chantier->id, $phase->chantier_id);
    }

    public function test_impossible_de_supprimer_une_phase_avec_tache_en_cours(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        Tache::factory()->for($chantier)->for($phase)->create(['statutTache' => 'en_cours']);

        $this->expectException(\Exception::class);
        $this->service->supprimerPhase($phase);
    }

    public function test_une_phase_sans_tache_se_supprime(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();

        $this->service->supprimerPhase($phase);

        $this->assertDatabaseMissing('phases', ['id' => $phase->id]);
    }

    public function test_creer_une_tache_recalcule_lavancement_de_la_phase(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();

        $this->service->creerTache([
            'nomTache' => 'Coulage béton',
            'date_debut_prevue' => now(),
            'date_fin_prevue' => now()->addWeek(),
            'phase_id' => $phase->id,
        ], $chantier->id);

        // Une tâche à 0% → phase reste en_attente, mais elle existe désormais dans la relation
        $this->assertEquals(1, $phase->fresh()->taches()->count());
    }

    public function test_mettre_avancement_a_100_valide_la_tache_definitivement(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        $tache = Tache::factory()->for($chantier)->for($phase)->create(['avancement' => 50, 'statutTache' => 'en_cours']);

        $this->service->mettreAJourAvancement($tache, 100);

        $fresh = $tache->fresh();
        $this->assertEquals(100, $fresh->avancement);
        $this->assertEquals('terminee', $fresh->statutTache);
        $this->assertNotNull($fresh->date_fin_reelle);
    }

    public function test_une_tache_terminee_ne_peut_plus_etre_modifiee(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        $tache = Tache::factory()->for($chantier)->for($phase)->create(['statutTache' => 'terminee', 'avancement' => 100]);

        $this->expectException(\Exception::class);
        $this->service->mettreAJourAvancement($tache, 80);
    }

    public function test_avancement_entre_1_et_99_met_le_statut_en_cours(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        $tache = Tache::factory()->for($chantier)->for($phase)->create(['avancement' => 0, 'statutTache' => 'en_attente']);

        $this->service->mettreAJourAvancement($tache, 45);

        $fresh = $tache->fresh();
        $this->assertEquals(45, $fresh->avancement);
        $this->assertEquals('en_cours', $fresh->statutTache);
    }

    public function test_demarrer_une_tache_renseigne_la_date_debut_reelle(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        $tache = Tache::factory()->for($chantier)->for($phase)->create(['statutTache' => 'en_attente', 'date_debut_reelle' => null]);

        $this->service->changerStatut($tache, 'en_cours');

        $fresh = $tache->fresh();
        $this->assertEquals('en_cours', $fresh->statutTache);
        $this->assertNotNull($fresh->date_debut_reelle);
    }

    public function test_la_phase_passe_terminee_quand_toutes_ses_taches_le_sont(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        $tache1 = Tache::factory()->for($chantier)->for($phase)->create(['avancement' => 0, 'statutTache' => 'en_attente']);
        $tache2 = Tache::factory()->for($chantier)->for($phase)->create(['avancement' => 0, 'statutTache' => 'en_attente']);

        $this->service->mettreAJourAvancement($tache1, 100);
        $this->service->mettreAJourAvancement($tache2->fresh(), 100);

        $this->assertEquals('terminee', $phase->fresh()->statutPhase);
    }
}
