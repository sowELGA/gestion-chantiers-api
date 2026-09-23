<?php

namespace Tests\Unit\Services;

use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Poste;
use App\Models\TauxSalaire;
use App\Services\PosteService;
use Tests\TestCase;

class PosteServiceTest extends TestCase
{
    private PosteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PosteService();
    }

    public function test_creer_un_poste(): void
    {
        $poste = $this->service->creer(['libelle' => 'Maçon']);

        $this->assertDatabaseHas('postes', ['libelle' => 'Maçon']);
        $this->assertEquals('Maçon', $poste->libelle);
    }

    public function test_supprimer_un_poste_sans_ouvrier_ni_taux(): void
    {
        $poste = Poste::factory()->create();

        $this->service->supprimer($poste);

        $this->assertDatabaseMissing('postes', ['id' => $poste->id]);
    }

    public function test_impossible_de_supprimer_un_poste_avec_ouvrier_actif(): void
    {
        $poste = Poste::factory()->create();
        Ouvrier::factory()->create(['poste_id' => $poste->id, 'statutOuvrier' => 'actif']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/personnel/');

        $this->service->supprimer($poste);
    }

    public function test_un_poste_reste_supprimable_si_seul_un_ouvrier_archive_y_est_rattache(): void
    {
        $poste = Poste::factory()->create();
        $ouvrier = Ouvrier::factory()->create(['poste_id' => $poste->id, 'statutOuvrier' => 'inactif']);
        $ouvrier->delete(); // soft delete

        $this->service->supprimer($poste);

        $this->assertDatabaseMissing('postes', ['id' => $poste->id]);
    }

    public function test_impossible_de_supprimer_un_poste_avec_taux_salarial_configure(): void
    {
        $chantier = Chantier::factory()->create();
        $poste = Poste::factory()->create();
        TauxSalaire::factory()->create(['chantier_id' => $chantier->id,'poste_id' => $poste->id]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/taux salariaux/');

        $this->service->supprimer($poste);
    }
}
