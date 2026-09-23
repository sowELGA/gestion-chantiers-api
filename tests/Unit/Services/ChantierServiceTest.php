<?php

namespace Tests\Unit\Services;

use App\Models\Chantier;
use App\Models\DepensesChantier;
use App\Models\Ouvrier;
use App\Models\Phase;
use App\Models\Poste;
use App\Services\ChantierService;
use Tests\TestCase;

class ChantierServiceTest extends TestCase
{
    private ChantierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChantierService();
    }

    public function test_un_chantier_sans_rien_se_supprime_sans_confirmation(): void
    {
        $chantier = Chantier::factory()->create();

        $this->service->supprimer($chantier, confirme: false);

        $this->assertDatabaseMissing('chantiers', ['id' => $chantier->id]);
    }

    public function test_un_chantier_avec_phase_sans_avancement_exige_une_confirmation(): void
    {
        $chantier = Chantier::factory()->create();
        Phase::factory()->for($chantier)->create(); // aucune tâche → avancement = 0

        $this->expectExceptionMessageMatches('/CONFIRMATION_REQUISE/');
        $this->service->supprimer($chantier, confirme: false);
    }

    public function test_un_chantier_avec_phase_sans_avancement_se_supprime_apres_confirmation(): void
    {
        $chantier = Chantier::factory()->create();
        Phase::factory()->for($chantier)->create();

        $this->service->supprimer($chantier, confirme: true);

        $this->assertDatabaseMissing('chantiers', ['id' => $chantier->id]);
    }

    public function test_un_chantier_avec_phase_avancee_est_definitivement_bloque(): void
    {
        $chantier = Chantier::factory()->create();
        $phase = Phase::factory()->for($chantier)->create();
        \App\Models\Tache::factory()->for($chantier)->for($phase)->create(['avancement' => 50]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/avancement en cours/');

        // Même avec confirmation, ça doit rester bloqué (ce n'est pas contournable)
        $this->service->supprimer($chantier, confirme: true);
    }

    public function test_un_chantier_avec_depenses_est_toujours_bloque(): void
    {
        $chantier = Chantier::factory()->create();
        DepensesChantier::factory()->for($chantier)->create();

        $this->expectExceptionMessageMatches('/dépenses/');
        $this->service->supprimer($chantier, confirme: true);
    }

    public function test_supprimer_un_chantier_libere_ses_ouvriers_sans_les_supprimer(): void
    {
        $chantier = Chantier::factory()->create();
        $poste = Poste::factory()->create();
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $this->service->supprimer($chantier, confirme: true);

        $this->assertDatabaseHas('ouvriers', ['id' => $ouvrier->id]); // toujours là
        $this->assertNull($ouvrier->fresh()->chantier_id); // mais libéré
    }
}
