<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OuvrierFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'nomOuvrier' => $this->faker->lastName(),
            'prenomOuvrier' => $this->faker->firstName(),
            'telOuvrier' => $this->faker->phoneNumber(),
            'statutOuvrier' => 'actif',
        ];
    }
}
