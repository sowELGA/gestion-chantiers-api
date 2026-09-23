<?php

namespace Tests\Feature\Rh;

use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Poste;
use App\Models\RecapHebdomadaire;
use App\Models\TauxSalaire;
use App\Services\PointageService;
use App\Services\RecapService;
use Carbon\Carbon;
use Tests\TestCase;

class SalairesWorkflowTest extends TestCase
{
    public function test_un_recap_valide_par_le_cp_apparait_dans_la_liste_rh(): void
    {
        $rh = $this->creerUserAvecRole('responsable_rh');
        $chantier = Chantier::factory()->enCours()->create();
        $poste = Poste::factory()->create();
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $semaine = \App\Helpers\SemaineHelper::numeroCycle(Carbon::today());
        $annee = \App\Helpers\SemaineHelper::anneeCycle(Carbon::today());

        RecapHebdomadaire::factory()->create([
            'chantier_id' => $chantier->id,
            'ouvrier_id' => $ouvrier->id,
            'semaine' => $semaine,
            'annee' => $annee,
            'statutRecap' => 'validee_cp',
        ]);

        $response = $this->connecterEnTantQue($rh)->getJson("/api/rh/salaires?semaine={$semaine}&annee={$annee}");

        $response->assertOk();
        $noms = collect($response->json('chantiers'))->pluck('chantier.nomChantier')->all();
        $this->assertContains($chantier->nomChantier, $noms);
    }

    public function test_lapercu_calcule_bien_le_total_general_avec_les_taux(): void
    {
        $rh = $this->creerUserAvecRole('responsable_rh');
        $chantier = Chantier::factory()->enCours()->create();
        $poste = Poste::factory()->create();
        TauxSalaire::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id, 'taux_journalier' => 6000, 'taux_heure_sup' => 1000]);
        $ouvrier = Ouvrier::factory()->create(['chantier_id' => $chantier->id, 'poste_id' => $poste->id]);

        $semaine = \App\Helpers\SemaineHelper::numeroCycle(Carbon::today());
        $annee = \App\Helpers\SemaineHelper::anneeCycle(Carbon::today());

        // Enregistre un pointage réel (avec snapshot du taux) via le service, comme en conditions réelles
        (new PointageService())->enregistrerFiche(
            [['ouvrier_id' => $ouvrier->id, 'statutPointage' => 'present', 'heures_sup' => 1]],
            $chantier->id,
            new RecapService()
        );

        RecapHebdomadaire::where('ouvrier_id', $ouvrier->id)->update(['statutRecap' => 'validee_cp']);

        $response = $this->connecterEnTantQue($rh)->getJson("/api/rh/salaires/{$chantier->id}/apercu?semaine={$semaine}&annee={$annee}");

        $response->assertOk();
        $this->assertEquals(7000, $response->json('total_general')); // 6000 base + 1000 h.sup
        $this->assertEquals(1, $response->json('nb_ouvriers'));
    }

    public function test_un_chef_de_projet_ne_peut_pas_acceder_aux_salaires(): void
    {
        $chefProjet = $this->creerUserAvecRole('chef_projet');

        $response = $this->connecterEnTantQue($chefProjet)->getJson('/api/rh/salaires');

        $response->assertStatus(403);
    }
}
