<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\Poste;
use App\Models\TauxSalaire;
use Illuminate\Database\Seeder;

class TauxSalaireSeeder extends Seeder
{
    public function run(): void
    {
        $chantier1 = Chantier::where('nomChantier', '3M')->first();
        $chantier2 = Chantier::where('nomChantier', 'Al Makhtoum')->first();

        $postes = Poste::pluck('id', 'libelle');

        $taux = [
            'Pointeur'         => ['journalier' => 6000, 'heure_sup' => 1500],
            'Chef Maçon'       => ['journalier' => 7000, 'heure_sup' => 1800],
            'Maçon'            => ['journalier' =>  5000, 'heure_sup' => 1500],
            'Chef Coffreur'    => ['journalier' => 6000, 'heure_sup' => 1750],
            'Coffreur'         => ['journalier' =>  4500, 'heure_sup' => 1000],
            'Grutier'          => ['journalier' => 6000, 'heure_sup' => 1500],
            'Chef Ferrailleur' => ['journalier' => 5000, 'heure_sup' => 1500],
            'Ferrailleur'      => ['journalier' =>  4000, 'heure_sup' => 1000],
            'Manœuvre'         => ['journalier' =>  4000, 'heure_sup' =>  800],
            'Chef Électricien' => ['journalier' => 7000, 'heure_sup' => 2000],
            'Électricien'      => ['journalier' => 5000, 'heure_sup' => 1500],
            'Plombier'         => ['journalier' => 7000, 'heure_sup' => 1800],
            'Carreleur'        => ['journalier' =>  5000, 'heure_sup' => 1000],
            'Peintre'          => ['journalier' =>  6000, 'heure_sup' => 1500],
        ];

        foreach ([$chantier1, $chantier2] as $chantier) {
            foreach ($taux as $poste => $values) {
                if (!isset($postes[$poste])) continue;
                TauxSalaire::create([
                    'taux_journalier' => $values['journalier'],
                    'taux_heure_sup'  => $values['heure_sup'],
                    'poste_id'        => $postes[$poste],
                    'chantier_id'     => $chantier->id,
                ]);
            }
        }
    }
}
