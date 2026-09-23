<?php

namespace Tests\Unit\Services;

use App\Models\Chantier;
use App\Models\Poste;
use App\Models\TauxSalaire;
use App\Services\TauxSalaireService;
use Tests\TestCase;

class TauxSalaireServiceTest extends TestCase
{
    private TauxSalaireService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TauxSalaireService();
    }

    public function test_enregistrer_un_taux_pour_un_poste(): void
    {
        $chantier = Chantier::factory()->create();
        $poste = Poste::factory()->create();

        $this->service->enregistrerTaux($chantier->id, [
            $poste->id => ['taux_journalier' => 7500, 'taux_heure_sup' => 1200],
        ]);

        $this->assertDatabaseHas('taux_salaires', [
            'chantier_id' => $chantier->id,
            'poste_id' => $poste->id,
            'taux_journalier' => 7500,
            'taux_heure_sup' => 1200,
        ]);
    }

    public function test_vider_les_deux_champs_supprime_le_taux_existant(): void
    {
        $chantier = Chantier::factory()->create();
        $poste = Poste::factory()->create();
        TauxSalaire::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $this->service->enregistrerTaux($chantier->id, [
            $poste->id => ['taux_journalier' => '', 'taux_heure_sup' => ''],
        ]);

        $this->assertDatabaseMissing('taux_salaires', ['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);
    }

    public function test_la_matrice_liste_tous_les_postes_meme_non_configures(): void
    {
        $chantier = Chantier::factory()->create();
        $posteConfigure = Poste::factory()->create(['libelle' => 'Maçon']);
        $posteNonConfigure = Poste::factory()->create(['libelle' => 'Électricien']);
        TauxSalaire::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $posteConfigure->id, 'taux_journalier' => 5000]);

        $matrice = $this->service->getMatriceTaux($chantier->id);

        $ligneConfiguree = $matrice->firstWhere('poste_id', $posteConfigure->id);
        $ligneNonConfiguree = $matrice->firstWhere('poste_id', $posteNonConfigure->id);

        $this->assertTrue($ligneConfiguree['configure']);
        $this->assertFalse($ligneNonConfiguree['configure']);
        $this->assertNull($ligneNonConfiguree['taux_journalier']);
    }

    public function test_mettre_a_jour_un_taux_existant_ecrase_lancien(): void
    {
        $chantier = Chantier::factory()->create();
        $poste = Poste::factory()->create();
        TauxSalaire::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id, 'taux_journalier' => 5000]);

        $this->service->enregistrerTaux($chantier->id, [
            $poste->id => ['taux_journalier' => 8000, 'taux_heure_sup' => 1500],
        ]);

        $this->assertDatabaseCount('taux_salaires', 1);
        $this->assertDatabaseHas('taux_salaires', ['taux_journalier' => 8000]);
    }
}
