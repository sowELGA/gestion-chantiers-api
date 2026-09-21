<?php

namespace Database\Seeders;

use App\Helpers\SemaineHelper;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\RecapHebdomadaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecapHebdomadaireSeeder extends Seeder
{
    public function run(): void
    {
        $chantiers = Chantier::whereIn('nomChantier', ['3M', 'Al Makhtoum'])->get();

        if ($chantiers->isEmpty()) {
            $this->command->error("Les chantiers '3M' et 'Al Makhtoum' doivent exister avant de lancer ce seeder.");
            return;
        }

        $statutsPasses = ['soumise', 'validee_cp', 'validee_cp', 'envoyee_direction'];
        $cycleActuel = SemaineHelper::debutCycle(Carbon::now());

        foreach ($chantiers as $chantier) {
            $pointeur   = $chantier->pointeur_id ? User::find($chantier->pointeur_id) : null;
            $chefProjet = $chantier->chef_projet_id ? User::find($chantier->chef_projet_id) : null;

            $ouvriers = Ouvrier::where('chantier_id', $chantier->id)
                ->where('statutOuvrier', 'actif')
                ->get();

            for ($i = 3; $i >= 0; $i--) {
                $samediCycle = $cycleActuel->copy()->subWeeks($i);
                $numeroSemaine = SemaineHelper::numeroCycle($samediCycle);
                $annee = SemaineHelper::anneeCycle($samediCycle);
                $estCycleEnCours = $i === 0;

                foreach ($ouvriers as $ouvrier) {
                    $statut = $estCycleEnCours
                        ? 'en_attente'
                        : $statutsPasses[array_rand($statutsPasses)];

                    $estValide = in_array($statut, ['validee_cp', 'envoyee_direction']);

                    RecapHebdomadaire::create([
                        'semaine'       => $numeroSemaine,
                        'annee'         => $annee,
                        'statutRecap'   => $statut,
                        'motif_rejet'   => null,
                        'valide_le'     => $estValide ? SemaineHelper::finDepuisNumero($numeroSemaine, $annee) : null,
                        'ouvrier_id'    => $ouvrier->id,
                        'chantier_id'   => $chantier->id,
                        'soumis_par_id' => $estCycleEnCours ? null : $pointeur?->id,
                        'valide_par_id' => $estValide ? $chefProjet?->id : null,
                    ]);
                }
            }
        }
    }
}
