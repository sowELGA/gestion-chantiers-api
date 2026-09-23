<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChantierFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'nomChantier' => 'Chantier ' . $this->faker->streetName(),
            'localisation' => $this->faker->city(),
            'budget_prevu' => $this->faker->numberBetween(10_000_000, 100_000_000),
            'date_debut' => now()->subMonth(),
            'date_fin_prevue' => now()->addMonths(6),
            'statut' => 'en_attente',
            'chef_projet_id' => null,
            'pointeur_id' => null,
        ];
    }

    public function enCours(): static
    {
        return $this->state(['statut' => 'en_cours']);
    }
}
