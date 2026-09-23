<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PosteFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return ['libelle' => 'Poste ' . $this->faker->unique()->word()];
    }
}
