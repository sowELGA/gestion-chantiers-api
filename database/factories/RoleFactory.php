<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleFactory extends Factory
{
    use HasFactory;
    
    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->word(),
            'libelle' => $this->faker->words(2, true),
            'exclusif' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(['nom' => 'admin', 'libelle' => 'Administrateur', 'exclusif' => false]);
    }

    public function directeurTravaux(): static
    {
        return $this->state(['nom' => 'directeur_travaux', 'libelle' => 'Directeur des travaux', 'exclusif' => false]);
    }

    public function daf(): static
    {
        return $this->state(['nom' => 'daf', 'libelle' => 'DAF', 'exclusif' => false]);
    }

    public function responsableRh(): static
    {
        return $this->state(['nom' => 'responsable_rh', 'libelle' => 'Responsable RH', 'exclusif' => false]);
    }

    public function chefProjet(): static
    {
        return $this->state(['nom' => 'chef_projet', 'libelle' => 'Chef de Projet', 'exclusif' => true]);
    }

    public function pointeur(): static
    {
        return $this->state(['nom' => 'pointeur', 'libelle' => 'Pointeur', 'exclusif' => true]);
    }
}
