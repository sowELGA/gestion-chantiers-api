<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nom' => Role::ADMIN,             'libelle' => 'Administrateur',                          'exclusif' => false],
            ['nom' => Role::DIRECTEUR_TRAVAUX, 'libelle' => 'Directeur des travaux',                    'exclusif' => false],
            ['nom' => Role::DAF,               'libelle' => 'Direction Administrative et Financière',  'exclusif' => false],
            ['nom' => Role::RESPONSABLE_RH,    'libelle' => 'Responsable des Ressources Humaines',      'exclusif' => false],
            ['nom' => Role::CHEF_PROJET,       'libelle' => 'Chef de Projet',                           'exclusif' => true],
            ['nom' => Role::POINTEUR,          'libelle' => 'Pointeur',                                 'exclusif' => true],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['nom' => $role['nom']], $role);
        }
    }
}
