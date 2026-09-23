<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhaseFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'nomPhase' => 'Phase ' . $this->faker->word(),
            'ordre' => 1,
            'typePhase' => 'gros_oeuvre',
            'date_debut' => now(),
            'date_fin_prevue' => now()->addMonth(),
            'statutPhase' => 'en_attente',
            'est_en_retard' => false,
        ];
    }
}
