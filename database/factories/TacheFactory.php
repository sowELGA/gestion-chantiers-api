<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TacheFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'nomTache' => 'Tâche ' . $this->faker->word(),
            'date_debut_prevue' => now(),
            'date_fin_prevue' => now()->addWeeks(2),
            'avancement' => 0,
            'statutTache' => 'en_attente',
            'est_en_retard' => false,
        ];
    }
}
