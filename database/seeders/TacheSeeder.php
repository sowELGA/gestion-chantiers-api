<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\Phase;
use App\Models\Tache;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TacheSeeder extends Seeder
{
    public function run(): void
    {
        $chantiers  = Chantier::whereIn('nomChantier', ['3M', 'Al Makhtoum'])->get();
        $chefProjet = User::where('email', 'chefprojet@dimagroupe.com')->first();

        if ($chantiers->isEmpty() || !$chefProjet) {
            $this->command->error("Chantiers et chef de projet requis avant ce seeder.");
            return;
        }

        foreach ($chantiers as $chantier) {
            $phases = Phase::where('chantier_id', $chantier->id)->orderBy('ordre')->get();

            foreach ($phases as $phase) {
                $debutPhase = Carbon::parse($phase->date_debut);
                $finPhase   = Carbon::parse($phase->date_fin_prevue);
                $milieu     = $debutPhase->copy()->addDays(intdiv($debutPhase->diffInDays($finPhase), 2));

                [$statut1, $avance1, $statut2, $avance2] = match ($phase->statutPhase) {
                    'terminee' => ['terminee', 100, 'terminee', 100],
                    'en_cours' => ['terminee', 100, 'en_cours', 50],
                    default    => ['en_attente', 0, 'en_attente', 0],
                };

                $tache1 = Tache::create([
                    'nomTache'            => $phase->nomPhase . ' — Étape 1',
                    'date_debut_prevue'   => $debutPhase->toDateString(),
                    'date_fin_prevue'     => $milieu->toDateString(),
                    'date_debut_reelle'   => $statut1 !== 'en_attente' ? $debutPhase->toDateString() : null,
                    'date_fin_reelle'     => $statut1 === 'terminee' ? $milieu->toDateString() : null,
                    'avancement'          => $avance1,
                    'statutTache'         => $statut1,
                    'est_en_retard'       => false,
                    'chantier_id'         => $chantier->id,
                    'phase_id'            => $phase->id,
                    'tache_precedente_id' => null,
                    'responsable_id'      => $chefProjet->id,
                ]);

                Tache::create([
                    'nomTache'            => $phase->nomPhase . ' — Étape 2',
                    'date_debut_prevue'   => $milieu->copy()->addDay()->toDateString(),
                    'date_fin_prevue'     => $finPhase->toDateString(),
                    'date_debut_reelle'   => $statut2 !== 'en_attente' ? $milieu->copy()->addDay()->toDateString() : null,
                    'date_fin_reelle'     => $statut2 === 'terminee' ? $finPhase->toDateString() : null,
                    'avancement'          => $avance2,
                    'statutTache'         => $statut2,
                    'est_en_retard'       => false,
                    'chantier_id'         => $chantier->id,
                    'phase_id'            => $phase->id,
                    'tache_precedente_id' => $tache1->id,
                    'responsable_id'      => $chefProjet->id,
                ]);
            }
        }
    }
}
