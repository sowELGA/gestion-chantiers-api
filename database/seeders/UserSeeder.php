<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'nom');

        // ═══════════════════════════════════════
        // Super Admin — cumule Admin, Directeur des travaux, DAF, Responsable RH
        // ═══════════════════════════════════════
        $superAdmin = User::create([
            'nomUser' => 'Ndiaye',
            'prenomUser' => 'Superadmin',
            'email' => 'superadmin@dimagroupe.com',
            'telUser' => '77 000 00 01',
            'password' => Hash::make('password123'),
            'premiere_connexion' => false,
            'actif' => true,
        ]);
        $superAdmin->roles()->attach([
            $roles[Role::ADMIN]             => ['gere_approvisionnements' => null, 'gere_depenses' => null],
            $roles[Role::DIRECTEUR_TRAVAUX] => ['gere_approvisionnements' => null, 'gere_depenses' => null],
            $roles[Role::DAF]               => ['gere_approvisionnements' => true, 'gere_depenses' => true],
            $roles[Role::RESPONSABLE_RH]    => ['gere_approvisionnements' => null, 'gere_depenses' => null],
        ]);

        // ═══════════════════════════════════════
        // Admin
        // ═══════════════════════════════════════
        $admin = User::create([
            'nomUser' => 'Sow',
            'prenomUser' => 'Awa',
            'email' => 'admin@dimagroupe.com',
            'telUser' => '77 000 00 02',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $admin->roles()->attach($roles[Role::ADMIN]);

        // ═══════════════════════════════════════
        // Directeur des travaux
        // ═══════════════════════════════════════
        $directeurTravaux = User::create([
            'nomUser' => 'Diagne',
            'prenomUser' => 'Moussa',
            'email' => 'directeurtravaux@dimagroupe.com',
            'telUser' => '77 000 00 03',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $directeurTravaux->roles()->attach($roles[Role::DIRECTEUR_TRAVAUX]);

        // ═══════════════════════════════════════
        // DAF
        // ═══════════════════════════════════════
        $daf = User::create([
            'nomUser' => 'Fall',
            'prenomUser' => 'Khady',
            'email' => 'daf@dimagroupe.com',
            'telUser' => '77 000 00 04',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $daf->roles()->attach($roles[Role::DAF], [
            'gere_approvisionnements' => true,
            'gere_depenses'           => true,
        ]);

        // ═══════════════════════════════════════
        // Responsable RH
        // ═══════════════════════════════════════
        $rh = User::create([
            'nomUser' => 'Diouf',
            'prenomUser' => 'Fatou',
            'email' => 'rh@dimagroupe.com',
            'telUser' => '77 000 00 05',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $rh->roles()->attach($roles[Role::RESPONSABLE_RH]);

        // ═══════════════════════════════════════
        // Chef de projet — Babacar Gueye (3M + Al Makhtoum)
        // ═══════════════════════════════════════
        $chefProjet = User::create([
            'nomUser' => 'Gueye',
            'prenomUser' => 'Babacar',
            'email' => 'chefprojet@dimagroupe.com',
            'telUser' => '77 000 00 06',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $chefProjet->roles()->attach($roles[Role::CHEF_PROJET]);

        // ═══════════════════════════════════════
        // Pointeur — 3M
        // ═══════════════════════════════════════
        $pointeur3M = User::create([
            'nomUser' => 'Dieng',
            'prenomUser' => 'Amadou',
            'email' => 'pointeur.3m@dimagroupe.com',
            'telUser' => '77 000 00 07',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $pointeur3M->roles()->attach($roles[Role::POINTEUR]);

        // ═══════════════════════════════════════
        // Pointeur — Al Makhtoum
        // ═══════════════════════════════════════
        $pointeurAlMakhtoum = User::create([
            'nomUser' => 'Kane',
            'prenomUser' => 'Ousmane',
            'email' => 'pointeur.almakhtoum@dimagroupe.com',
            'telUser' => '77 000 00 08',
            'password' => Hash::make('password123'),
            'premiere_connexion' => true,
            'actif' => true,
        ]);
        $pointeurAlMakhtoum->roles()->attach($roles[Role::POINTEUR]);
    }
}
