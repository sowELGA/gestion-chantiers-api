<?php

namespace Database\Seeders;

use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApprovisionnementSeeder extends Seeder
{
    public function run(): void
    {
        $chantiers  = Chantier::whereIn('nomChantier', ['3M', 'Al Makhtoum'])->get();
        $chefProjet = User::where('email', 'chefprojet@dimagroupe.com')->first();

        if ($chantiers->isEmpty() || !$chefProjet) {
            $this->command->error("Chantiers et chef de projet requis avant ce seeder.");
            return;
        }

        $demandes = [
            ['designation' => 'Ciment CPA 42.5',      'quantite_demandee' => 200, 'unite' => 'sac',   'priorite' => 'urgent', 'statutAppro' => 'cloturee'],
            ['designation' => 'Fer à béton 12mm',     'quantite_demandee' => 5,   'unite' => 'tonne', 'priorite' => 'urgent', 'statutAppro' => 'partiellement_recue'],
            ['designation' => 'Sable de construction', 'quantite_demandee' => 30,  'unite' => 'm3',    'priorite' => 'normal', 'statutAppro' => 'en_cours_livraison'],
            ['designation' => 'Peinture façade',      'quantite_demandee' => 100, 'unite' => 'litre', 'priorite' => 'normal', 'statutAppro' => 'validee'],
            ['designation' => 'Carreaux 60x60',       'quantite_demandee' => 500, 'unite' => 'm2',    'priorite' => 'normal', 'statutAppro' => 'en_attente'],
            ['designation' => 'Gravier',               'quantite_demandee' => 20,  'unite' => 'm3',    'priorite' => 'normal', 'statutAppro' => 'rejetee'],
        ];

        foreach ($chantiers as $chantier) {
            foreach ($demandes as $d) {
                $enTraitement = in_array($d['statutAppro'], ['en_cours_livraison', 'partiellement_recue', 'cloturee']);

                Approvisionnement::create([
                    'designation'              => $d['designation'],
                    'quantite_demandee'        => $d['quantite_demandee'],
                    'unite'                    => $d['unite'],
                    'priorite'                 => $d['priorite'],
                    'statutAppro'              => $d['statutAppro'],
                    'date_livraison_souhaitee' => now()->addDays(rand(5, 20))->toDateString(),
                    'date_commande'            => $enTraitement ? now()->subDays(rand(3, 10))->toDateString() : null,
                    'date_livraison_prevue'    => $enTraitement ? now()->addDays(rand(1, 5))->toDateString() : null,
                    'chantier_id'              => $chantier->id,
                    'demandeur_id'             => $chefProjet->id,
                ]);
            }
        }
    }
}
