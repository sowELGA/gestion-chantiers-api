<?php

namespace Tests\Unit\Services;

use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Models\User;
use App\Services\ApprovisionnementService;
use Tests\TestCase;

class ApprovisionnementServiceTest extends TestCase
{
    private ApprovisionnementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApprovisionnementService();
    }

    public function test_creer_plusieurs_demandes_en_une_fois(): void
    {
        $chantier = Chantier::factory()->create();
        $demandeur = User::factory()->create();

        $this->service->creerPlusieurs([
            'chantier_id' => $chantier->id,
            'demandes' => [
                ['designation' => 'Ciment', 'quantite_demandee' => 50, 'unite' => 'sac', 'date_livraison_souhaitee' => now()->addDays(10)],
                ['designation' => 'Sable', 'quantite_demandee' => 5, 'unite' => 'm3', 'date_livraison_souhaitee' => now()->addDay()],
            ],
        ], $demandeur->id);

        $this->assertDatabaseCount('approvisionnements', 2);
        $this->assertDatabaseHas('approvisionnements', ['designation' => 'Ciment', 'priorite' => 'normal']);
        $this->assertDatabaseHas('approvisionnements', ['designation' => 'Sable', 'priorite' => 'urgent']); // livraison dans 1 jour → urgent
    }

    public function test_valider_une_demande_en_attente(): void
    {
        $demande = Approvisionnement::factory()->create(['statutAppro' => 'en_attente']);

        $this->service->valider($demande);

        $this->assertEquals('validee', $demande->fresh()->statutAppro);
    }

    public function test_impossible_de_valider_une_demande_deja_validee(): void
    {
        $demande = Approvisionnement::factory()->create(['statutAppro' => 'validee']);

        $this->expectException(\Exception::class);
        $this->service->valider($demande);
    }

    public function test_commander_une_demande_validee(): void
    {
        $demande = Approvisionnement::factory()->create(['statutAppro' => 'validee']);

        $this->service->commander($demande, now()->addDays(3)->toDateString());

        $fresh = $demande->fresh();
        $this->assertEquals('en_cours_livraison', $fresh->statutAppro);
        $this->assertNotNull($fresh->date_commande);
    }

    public function test_impossible_de_commander_une_demande_en_attente(): void
    {
        $demande = Approvisionnement::factory()->create(['statutAppro' => 'en_attente']);

        $this->expectException(\Exception::class);
        $this->service->commander($demande);
    }

    public function test_supprimer_est_impossible_une_fois_en_livraison(): void
    {
        $demande = Approvisionnement::factory()->create(['statutAppro' => 'en_cours_livraison']);

        $this->expectException(\Exception::class);
        $this->service->supprimer($demande);
    }

    public function test_modifier_est_impossible_hors_statut_en_attente(): void
    {
        $demande = Approvisionnement::factory()->create(['statutAppro' => 'validee']);

        $this->expectException(\Exception::class);
        $this->service->modifier($demande, [
            'designation' => 'Nouveau',
            'quantite_demandee' => 1,
            'unite' => 'sac',
            'date_livraison_souhaitee' => now()->addDay(),
            'chantier_id' => Chantier::factory()->create()->id,
        ]);
    }
}
