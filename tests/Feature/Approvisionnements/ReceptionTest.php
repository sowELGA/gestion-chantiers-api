<?php

namespace Tests\Feature\Approvisionnements;

use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Services\ApprovisionnementService;
use Tests\TestCase;

class ReceptionTest extends TestCase
{
    private ApprovisionnementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApprovisionnementService();
    }

    public function test_une_reception_totale_cloture_la_demande(): void
    {
        $pointeur = $this->creerUserAvecRole('pointeur');
        $chantier = Chantier::factory()->create(['pointeur_id' => $pointeur->id]);
        $demande = Approvisionnement::factory()->create([
            'chantier_id' => $chantier->id,
            'quantite_demandee' => 100,
            'statutAppro' => 'en_cours_livraison',
        ]);

        $this->service->receptionner($demande, ['quantite_recue' => 100], $pointeur->id);

        $this->assertEquals('cloturee', $demande->fresh()->statutAppro);
    }

    public function test_une_reception_partielle_laisse_la_demande_ouverte(): void
    {
        $pointeur = $this->creerUserAvecRole('pointeur');
        $chantier = Chantier::factory()->create(['pointeur_id' => $pointeur->id]);
        $demande = Approvisionnement::factory()->create([
            'chantier_id' => $chantier->id,
            'quantite_demandee' => 100,
            'statutAppro' => 'en_cours_livraison',
        ]);

        $this->service->receptionner($demande, ['quantite_recue' => 60], $pointeur->id);

        $fresh = $demande->fresh();
        $this->assertEquals('partiellement_recue', $fresh->statutAppro);
        $this->assertEquals(40, $fresh->quantite_restante);
    }

    public function test_plusieurs_receptions_partielles_cumulent_jusqu_a_cloture(): void
    {
        $pointeur = $this->creerUserAvecRole('pointeur');
        $chantier = Chantier::factory()->create(['pointeur_id' => $pointeur->id]);
        $demande = Approvisionnement::factory()->create([
            'chantier_id' => $chantier->id,
            'quantite_demandee' => 100,
            'statutAppro' => 'en_cours_livraison',
        ]);

        $this->service->receptionner($demande, ['quantite_recue' => 60], $pointeur->id);
        $this->service->receptionner($demande->fresh(), ['quantite_recue' => 40], $pointeur->id);

        $this->assertEquals('cloturee', $demande->fresh()->statutAppro);
        $this->assertDatabaseCount('bon_receptions', 2);
    }

    public function test_impossible_de_receptionner_plus_que_la_quantite_restante(): void
    {
        $pointeur = $this->creerUserAvecRole('pointeur');
        $chantier = Chantier::factory()->create(['pointeur_id' => $pointeur->id]);
        $demande = Approvisionnement::factory()->create([
            'chantier_id' => $chantier->id,
            'quantite_demandee' => 100,
            'statutAppro' => 'en_cours_livraison',
        ]);

        $this->expectException(\Exception::class);
        $this->service->receptionner($demande, ['quantite_recue' => 150], $pointeur->id);
    }
}
