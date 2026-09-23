<?php

namespace Tests\Unit\Helpers;

use App\Helpers\PointageHelper;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Pointage;
use App\Models\Poste;
use Tests\TestCase;

class PointageHelperTest extends TestCase
{
    public function test_calcule_le_salaire_base_pour_les_jours_presents(): void
    {
        $chantier = Chantier::factory()->create();
        $poste = Poste::factory()->create();
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $pointages = collect([
            Pointage::factory()->make(['statutPointage' => 'present', 'taux_journalier' => 5000, 'heures_sup' => 0, 'taux_heure_sup' => 1000]),
            Pointage::factory()->make(['statutPointage' => 'present', 'taux_journalier' => 5000, 'heures_sup' => 2, 'taux_heure_sup' => 1000]),
            Pointage::factory()->make(['statutPointage' => 'absent', 'taux_journalier' => null, 'heures_sup' => 0, 'taux_heure_sup' => null]),
        ]);

        $resultat = PointageHelper::calculerSalaireDepuisPointages($pointages);

        $this->assertEquals(2, $resultat['jours_presents']);
        $this->assertEquals(2, $resultat['total_heures_sup']);
        $this->assertEquals(10000, $resultat['salaire_base']); // 2 jours × 5000
        $this->assertEquals(2000, $resultat['salaire_heures_sup']); // 2h × 1000
        $this->assertEquals(12000, $resultat['salaire_total']);
    }

    public function test_aucun_pointage_donne_un_salaire_nul(): void
    {
        $resultat = PointageHelper::calculerSalaireDepuisPointages(collect());

        $this->assertEquals(0, $resultat['jours_presents']);
        $this->assertEquals(0, $resultat['salaire_total']);
    }
}
