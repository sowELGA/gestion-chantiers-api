<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\DepensesChantier;
use Illuminate\Database\Seeder;

class DepenseChantierSeeder extends Seeder
{
    public function run(): void
    {
        $chantiers = Chantier::whereIn('nomChantier', ['3M', 'Al Makhtoum'])->get();

        if ($chantiers->isEmpty()) {
            $this->command->error("Les chantiers '3M' et 'Al Makhtoum' doivent exister avant ce seeder.");
            return;
        }

        $depenses = [
            ['categorie' => 'materiaux', 'montant' => 4500000, 'description' => 'Achat de ciment et fer à béton'],
            ['categorie' => 'materiels', 'montant' => 1200000, 'description' => "Location d'une bétonnière"],
            ['categorie' => 'salaires',  'montant' => 3800000, 'description' => 'Paie hebdomadaire des ouvriers'],
            ['categorie' => 'autre',     'montant' => 250000,  'description' => 'Frais de transport et logistique'],
        ];

        foreach ($chantiers as $chantier) {
            foreach ($depenses as $d) {
                DepensesChantier::create([
                    'categorie'    => $d['categorie'],
                    'montant'      => $d['montant'],
                    'description'  => $d['description'],
                    'date_depense' => now()->subDays(rand(1, 30))->toDateString(),
                    'chantier_id'  => $chantier->id,
                ]);
            }
        }
    }
}
