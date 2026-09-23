<?php

namespace Tests\Unit\Services;

use App\Helpers\SemaineHelper;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Poste;
use App\Models\Pointage;
use App\Models\TauxSalaire;
use App\Services\PointageService;
use App\Services\RecapService;
use Carbon\Carbon;
use Tests\TestCase;

class PointageServiceTest extends TestCase
{
    private PointageService $service;
    private RecapService $recapService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PointageService();
        $this->recapService = new RecapService();
    }

    public function test_enregistrer_une_fiche_prend_un_snapshot_du_taux_salarial(): void
    {
        $chantier = Chantier::factory()->enCours()->create();
        $poste = Poste::factory()->create();
        TauxSalaire::factory()->create(['poste_id' => $poste->id, 'chantier_id' => $chantier->id, 'taux_journalier' => 7500]);
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $this->service->enregistrerFiche([
            ['ouvrier_id' => $ouvrier->id, 'statutPointage' => 'present', 'heures_sup' => 2],
        ], $chantier->id, $this->recapService);

        $pointage = Pointage::where('ouvrier_id', $ouvrier->id)->first();
        $this->assertEquals(7500, $pointage->taux_journalier);

        // Le taux change ensuite sur le référentiel, mais le pointage passé doit garder SON snapshot
        TauxSalaire::where('poste_id', $poste->id)->update(['taux_journalier' => 9999]);
        $this->assertEquals(7500, $pointage->fresh()->taux_journalier);
    }

    public function test_un_ouvrier_absent_n_a_pas_de_taux_enregistre(): void
    {
        $chantier = Chantier::factory()->enCours()->create();
        $poste = Poste::factory()->create();
        TauxSalaire::factory()->create(['poste_id' => $poste->id, 'chantier_id' => $chantier->id]);
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $this->service->enregistrerFiche([
            ['ouvrier_id' => $ouvrier->id, 'statutPointage' => 'absent'],
        ], $chantier->id, $this->recapService);

        $pointage = Pointage::where('ouvrier_id', $ouvrier->id)->first();
        $this->assertNull($pointage->taux_journalier);
        $this->assertEquals(0, $pointage->heures_sup);
    }

    public function test_enregistrer_une_fiche_cree_automatiquement_le_recap_de_la_semaine(): void
    {
        $chantier = Chantier::factory()->enCours()->create();
        $poste = Poste::factory()->create();
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $this->service->enregistrerFiche([
            ['ouvrier_id' => $ouvrier->id, 'statutPointage' => 'present'],
        ], $chantier->id, $this->recapService);

        $this->assertDatabaseHas('recaps_hebdomadaires', [
            'ouvrier_id' => $ouvrier->id,
            'chantier_id' => $chantier->id,
            'statutRecap' => 'en_attente',
        ]);
    }

    public function test_impossible_de_pointer_une_semaine_deja_soumise(): void
    {
        $chantier = Chantier::factory()->enCours()->create();
        $poste = Poste::factory()->create();
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $semaine = SemaineHelper::numeroCycle(Carbon::today());
        $annee = SemaineHelper::anneeCycle(Carbon::today());

        \App\Models\RecapHebdomadaire::factory()->create([
            'chantier_id' => $chantier->id,
            'ouvrier_id' => $ouvrier->id,
            'semaine' => $semaine,
            'annee' => $annee,
            'statutRecap' => 'soumise',
        ]);

        $this->expectException(\Exception::class);
        $this->service->enregistrerFiche([
            ['ouvrier_id' => $ouvrier->id, 'statutPointage' => 'present'],
        ], $chantier->id, $this->recapService);
    }
}
