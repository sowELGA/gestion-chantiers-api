<?php

namespace Tests\Unit\Services;

use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Poste;
use App\Services\OuvrierService;
use App\Services\PersonnelService;
use Tests\TestCase;

class OuvrierServiceTest extends TestCase
{
    private OuvrierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OuvrierService();
    }

    public function test_creer_un_ouvrier_actif_par_defaut(): void
    {
        $poste = Poste::factory()->create();
        $chantier = Chantier::factory()->create();

        $ouvrier = $this->service->creer([
            'nomOuvrier' => 'Diop',
            'prenomOuvrier' => 'Moussa',
            'telOuvrier' => '770000000',
            'poste_id' => $poste->id,
            'chantier_id' => $chantier->id,
        ]);

        $this->assertEquals('actif', $ouvrier->statutOuvrier);
    }

    public function test_toggle_statut_bascule_actif_inactif(): void
    {
        $ouvrier = Ouvrier::factory()->create(['statutOuvrier' => 'actif']);

        $this->service->toggleStatut($ouvrier);
        $this->assertEquals('inactif', $ouvrier->fresh()->statutOuvrier);

        $this->service->toggleStatut($ouvrier->fresh());
        $this->assertEquals('actif', $ouvrier->fresh()->statutOuvrier);
    }

    public function test_impossible_de_supprimer_un_ouvrier_actif(): void
    {
        $ouvrier = Ouvrier::factory()->create(['statutOuvrier' => 'actif']);

        $this->expectException(\Exception::class);
        $this->service->supprimer($ouvrier);
    }

    public function test_supprimer_un_ouvrier_inactif_est_un_soft_delete(): void
    {
        $ouvrier = Ouvrier::factory()->create(['statutOuvrier' => 'inactif']);

        $this->service->supprimer($ouvrier);

        // La ligne existe toujours en base (soft delete), mais n'apparaît plus dans les requêtes normales
        $this->assertSoftDeleted('ouvriers', ['id' => $ouvrier->id]);
        $this->assertNull(Ouvrier::find($ouvrier->id));
        $this->assertNotNull(Ouvrier::withTrashed()->find($ouvrier->id));
    }

    public function test_un_ouvrier_peut_ne_pas_avoir_de_chantier(): void
    {
        $poste = Poste::factory()->create();

        $ouvrier = $this->service->creer([
            'nomOuvrier' => 'Sow',
            'prenomOuvrier' => 'Awa',
            'telOuvrier' => '771111111',
            'poste_id' => $poste->id,
            'chantier_id' => null,
        ]);

        $this->assertNull($ouvrier->chantier_id);
    }
}
