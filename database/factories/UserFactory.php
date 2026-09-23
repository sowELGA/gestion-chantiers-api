<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'nomUser' => $this->faker->lastName(),
            'prenomUser' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'telUser' => $this->faker->phoneNumber(),
            'password' => Hash::make('password'),
            'premiere_connexion' => false,
            'actif' => true,
        ];
    }

    public function premiereConnexion(): static
    {
        return $this->state(['premiere_connexion' => true]);
    }

    public function inactif(): static
    {
        return $this->state(['actif' => false]);
    }
}
