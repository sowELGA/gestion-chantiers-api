<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TauxSalaireFactory extends Factory
{
    use HasFactory;

    public function definition(): array
    {
        return ['taux_journalier' => 5000, 'taux_heure_sup' => 1000];
    }
}
