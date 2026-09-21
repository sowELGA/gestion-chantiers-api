<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserChantierSeeder extends Seeder
{
    public function run(): void
    {
        $chantier3M         = Chantier::where('nomChantier', '3M')->first();
        $chantierAlMakhtoum = Chantier::where('nomChantier', 'Al Makhtoum')->first();
        $chefProjet         = User::where('email', 'chefprojet@dimagroupe.com')->first();
        $pointeur3M         = User::where('email', 'pointeur.3m@dimagroupe.com')->first();
        $pointeurAlMakhtoum = User::where('email', 'pointeur.almakhtoum@dimagroupe.com')->first();

        if (!$chantier3M || !$chantierAlMakhtoum || !$chefProjet || !$pointeur3M || !$pointeurAlMakhtoum) {
            $this->command->error("Chantiers et utilisateurs requis absents — lancez ChantierSeeder et UserSeeder avant.");
            return;
        }

        $affectations = [
            ['user_id' => $chefProjet->id,         'chantier_id' => $chantier3M->id,         'debut' => $chantier3M->date_debut],
            ['user_id' => $chefProjet->id,         'chantier_id' => $chantierAlMakhtoum->id, 'debut' => $chantierAlMakhtoum->date_debut],
            ['user_id' => $pointeur3M->id,         'chantier_id' => $chantier3M->id,         'debut' => $chantier3M->date_debut],
            ['user_id' => $pointeurAlMakhtoum->id, 'chantier_id' => $chantierAlMakhtoum->id, 'debut' => $chantierAlMakhtoum->date_debut],
        ];

        foreach ($affectations as $a) {
            DB::table('user_chantier')->insert([
                'user_id'            => $a['user_id'],
                'chantier_id'        => $a['chantier_id'],
                'debut_affectation'  => $a['debut'],
                'fin_affectation'    => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }
}
