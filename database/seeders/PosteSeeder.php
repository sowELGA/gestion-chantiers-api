<?php

namespace Database\Seeders;

use App\Models\Poste;
use Illuminate\Database\Seeder;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        $postes = [
            'Pointeur',
            'Chef Maçon',
            'Maçon',
            'Chef Coffreur',
            'Coffreur',
            'Grutier',
            'Chef Ferrailleur',
            'Ferrailleur',
            'Manœuvre',
            'Chef Électricien',
            'Électricien',
            'Plombier',
            'Carreleur',
            'Peintre',
        ];

        foreach ($postes as $libelle) {
            Poste::firstOrCreate(['libelle' => $libelle]);
        }
    }
}
