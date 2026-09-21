<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PosteSeeder::class,
            ChantierSeeder::class,
            UserChantierSeeder::class,
            OuvrierSeeder::class,
            TauxSalaireSeeder::class,
            PhaseSeeder::class,
            TacheSeeder::class,
            ApprovisionnementSeeder::class,
            BonReceptionSeeder::class,
            DepenseChantierSeeder::class,
            PointageSeeder::class,
            RecapHebdomadaireSeeder::class,
            RapportChantierSeeder::class,
        ]);
    }
}
