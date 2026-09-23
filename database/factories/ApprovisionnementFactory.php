<?php

namespace Database\Factories;

use App\Models\Chantier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovisionnementFactory extends Factory
{
    use HasFactory;

    public function definition(): array
    {
        return [
            'designation' => 'Ciment 50kg',
            'quantite_demandee' => 100,
            'unite' => 'sac',
            'priorite' => 'normal',
            'statutAppro' => 'en_attente',
            'date_livraison_souhaitee' => now()->addDays(5),
            'chantier_id' => Chantier::factory(),
            'demandeur_id' => User::factory(),
        ];
    }
}
