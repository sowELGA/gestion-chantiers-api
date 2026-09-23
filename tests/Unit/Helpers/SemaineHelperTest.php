<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SemaineHelper;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class SemaineHelperTest extends TestCase
{
    public function test_le_cycle_commence_toujours_un_samedi(): void
    {
        // Mercredi 24 septembre 2026
        $mercredi = Carbon::parse('2026-09-24');
        $debut = SemaineHelper::debutCycle($mercredi);

        $this->assertEquals(Carbon::SATURDAY, $debut->dayOfWeek);
        $this->assertEquals('2026-09-19', $debut->toDateString()); // samedi précédent
    }

    public function test_le_cycle_se_termine_un_vendredi_7_jours_apres(): void
    {
        $mercredi = Carbon::parse('2026-09-24');
        $fin = SemaineHelper::finCycle($mercredi);

        $this->assertEquals(Carbon::FRIDAY, $fin->dayOfWeek);
        $this->assertEquals('2026-09-25', $fin->toDateString());
    }

    public function test_un_samedi_est_deja_le_debut_de_son_propre_cycle(): void
    {
        $samedi = Carbon::parse('2026-09-19');
        $debut = SemaineHelper::debutCycle($samedi);

        $this->assertEquals('2026-09-19', $debut->toDateString());
    }

    public function test_jours_retourne_bien_7_dates_consecutives(): void
    {
        $jours = SemaineHelper::jours(39, 2026);

        $this->assertCount(7, $jours);
        $this->assertEquals(Carbon::SATURDAY, $jours[0]->dayOfWeek);
        $this->assertEquals(Carbon::FRIDAY, $jours[6]->dayOfWeek);
    }
}
