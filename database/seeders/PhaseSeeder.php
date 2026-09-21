<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\Phase;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PhaseSeeder extends Seeder
{
    public function run(): void
    {
        $chantiers = Chantier::whereIn('nomChantier', ['3M', 'Al Makhtoum'])->get();

        if ($chantiers->isEmpty()) {
            $this->command->error("Les chantiers '3M' et 'Al Makhtoum' doivent exister avant ce seeder.");
            return;
        }

        $definitions = [
            ['nomPhase' => 'Fondations',    'typePhase' => 'gros_oeuvre',   'ordre' => 1, 'statutPhase' => 'terminee'],
            ['nomPhase' => 'Élévation',     'typePhase' => 'gros_oeuvre',   'ordre' => 2, 'statutPhase' => 'en_cours'],
            ['nomPhase' => 'Second œuvre',  'typePhase' => 'second_oeuvre', 'ordre' => 3, 'statutPhase' => 'en_attente'],
            ['nomPhase' => 'Finitions',     'typePhase' => 'finitions',     'ordre' => 4, 'statutPhase' => 'en_attente'],
        ];

        foreach ($chantiers as $chantier) {
            $debut       = Carbon::parse($chantier->date_debut);
            $fin         = Carbon::parse($chantier->date_fin_prevue);
            $dureeTotale = $debut->diffInDays($fin);
            $dureePhase  = max(1, intdiv($dureeTotale, count($definitions)));

            foreach ($definitions as $i => $def) {
                $dateDebutPhase = $debut->copy()->addDays($i * $dureePhase);
                $dateFinPhase   = $dateDebutPhase->copy()->addDays($dureePhase - 1);

                Phase::create([
                    'nomPhase'        => $def['nomPhase'],
                    'typePhase'       => $def['typePhase'],
                    'sous_traitant'   => null,
                    'ordre'           => $def['ordre'],
                    'date_debut'      => $dateDebutPhase->toDateString(),
                    'date_fin_prevue' => $dateFinPhase->toDateString(),
                    'statutPhase'     => $def['statutPhase'],
                    'est_en_retard'   => false,
                    'chantier_id'     => $chantier->id,
                ]);
            }
        }
    }
}
