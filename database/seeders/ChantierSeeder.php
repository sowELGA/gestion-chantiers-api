<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChantierSeeder extends Seeder
{
    public function run(): void
    {
        $chefProjet         = User::where('email', 'chefprojet@dimagroupe.com')->first();
        $pointeur3M         = User::where('email', 'pointeur.3m@dimagroupe.com')->first();
        $pointeurAlMakhtoum = User::where('email', 'pointeur.almakhtoum@dimagroupe.com')->first();

        if (!$chefProjet || !$pointeur3M || !$pointeurAlMakhtoum) {
            $this->command->error("Le chef de projet et les deux pointeurs doivent exister avant ce seeder.");
            return;
        }

        // En attente — pas encore assigné
        Chantier::create([
            'nomChantier'     => 'Résidence Ngor',
            'localisation'    => 'Ngor, Dakar',
            'budget_prevu'    => 350000000,
            'date_debut'      => now()->addMonth()->toDateString(),
            'date_fin_prevue' => now()->addMonths(10)->toDateString(),
            'date_fin_reelle' => null,
            'statut'          => 'en_attente',
            'chef_projet_id'  => null,
            'pointeur_id'     => null,
        ]);

        // En cours — 3M
        Chantier::create([
            'nomChantier'     => '3M',
            'localisation'    => 'Liberté 1, Dakar',
            'budget_prevu'    => 500000000,
            'date_debut'      => '2026-01-15',
            'date_fin_prevue' => '2026-12-31',
            'date_fin_reelle' => null,
            'statut'          => 'en_cours',
            'chef_projet_id'  => $chefProjet->id,
            'pointeur_id'     => $pointeur3M->id,
        ]);

        // En cours — Al Makhtoum
        Chantier::create([
            'nomChantier'     => 'Al Makhtoum',
            'localisation'    => 'Sacré Cœur, Dakar',
            'budget_prevu'    => 600000000,
            'date_debut'      => '2025-06-01',
            'date_fin_prevue' => '2026-08-31',
            'date_fin_reelle' => null,
            'statut'          => 'en_cours',
            'chef_projet_id'  => $chefProjet->id,
            'pointeur_id'     => $pointeurAlMakhtoum->id,
        ]);

        // Suspendu
        Chantier::create([
            'nomChantier'     => 'Immeuble Fann',
            'localisation'    => 'Fann, Dakar',
            'budget_prevu'    => 280000000,
            'date_debut'      => '2025-11-01',
            'date_fin_prevue' => '2026-10-31',
            'date_fin_reelle' => null,
            'statut'          => 'suspendu',
            'chef_projet_id'  => null,
            'pointeur_id'     => null,
        ]);

        // Livré
        Chantier::create([
            'nomChantier'     => 'Cité Keur Gorgui',
            'localisation'    => 'Mermoz, Dakar',
            'budget_prevu'    => 420000000,
            'date_debut'      => '2024-09-01',
            'date_fin_prevue' => '2025-08-31',
            'date_fin_reelle' => '2025-09-10',
            'statut'          => 'livre',
            'chef_projet_id'  => null,
            'pointeur_id'     => null,
        ]);
    }
}
