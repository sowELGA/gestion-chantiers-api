<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepensesChantierFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'categorie' => 'materiaux',
            'montant' => $this->faker->numberBetween(10000, 500000),
            'description' => 'Dépense test',
            'date_depense' => now(),
        ];
    }
}
