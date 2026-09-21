<?php

namespace Database\Seeders;

use App\Models\Approvisionnement;
use App\Models\BonReception;
use App\Models\Chantier;
use App\Models\User;
use Illuminate\Database\Seeder;

class BonReceptionSeeder extends Seeder
{
    public function run(): void
    {
        $chantier3M         = Chantier::where('nomChantier', '3M')->first();
        $chantierAlMakhtoum = Chantier::where('nomChantier', 'Al Makhtoum')->first();
        $pointeur3M         = User::where('email', 'pointeur.3m@dimagroupe.com')->first();
        $pointeurAlMakhtoum = User::where('email', 'pointeur.almakhtoum@dimagroupe.com')->first();

        if (!$chantier3M || !$chantierAlMakhtoum || !$pointeur3M || !$pointeurAlMakhtoum) {
            $this->command->error("Chantiers et pointeurs requis avant ce seeder.");
            return;
        }

        $receptionnaires = [
            $chantier3M->id         => $pointeur3M,
            $chantierAlMakhtoum->id => $pointeurAlMakhtoum,
        ];

        $demandes = Approvisionnement::whereIn('chantier_id', [$chantier3M->id, $chantierAlMakhtoum->id])
            ->whereIn('statutAppro', ['cloturee', 'partiellement_recue'])
            ->get();

        foreach ($demandes as $demande) {
            $quantiteRecue = $demande->statutAppro === 'cloturee'
                ? $demande->quantite_demandee
                : round($demande->quantite_demandee * 0.6, 2);

            BonReception::create([
                'quantite_recue'      => $quantiteRecue,
                'date_reception'      => now()->subDays(rand(1, 5))->toDateString(),
                'observation'         => $demande->statutAppro === 'partiellement_recue'
                    ? 'Livraison incomplète, reliquat attendu.'
                    : null,
                'demande_id'          => $demande->id,
                'chantier_id'         => $demande->chantier_id,
                'receptionnee_par_id' => $receptionnaires[$demande->chantier_id]->id,
            ]);
        }
    }
}
