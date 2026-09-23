<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecapHebdomadaireFactory extends Factory
{
    use HasFactory;

    public function definition(): array
    {
        return [
            'semaine' => now()->isoWeek(),
            'annee' => now()->year,
            'statutRecap' => 'en_attente',
        ];
    }
}
