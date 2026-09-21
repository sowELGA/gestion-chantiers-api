<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Poste;
use Illuminate\Database\Seeder;

class OuvrierSeeder extends Seeder
{
    public function run(): void
    {
        $chantier1 = Chantier::where('nomChantier', '3M')->first();
        $chantier2 = Chantier::where('nomChantier', 'Al Makhtoum')->first();

        if (!$chantier1 || !$chantier2) {
            $this->command->error("Les chantiers '3M' et 'Al Makhtoum' doivent exister avant de lancer ce seeder.");
            return;
        }

        $postes = Poste::pluck('id', 'libelle');

        // Personnel du chantier 1 (3M) — 35 ouvriers
        $personnel1 = [
            ['Dieng',      'Amadou',      'Pointeur'],

            ['Thiaw',      'Modou',       'Chef Maçon'],
            ['Adama',      'El Hadj',     'Maçon'],
            ['Cissé',      'Abdoulaye',   'Maçon'],
            ['Fall',       'Mamadou',     'Maçon'],
            ['Sow',        'Cheikh',      'Maçon'],
            ['Ba',         'Ibrahima',    'Maçon'],
            ['Ndiaye',     'Moussa',      'Maçon'],
            ['Gueye',      'Mory',        'Maçon'],

            ['Ndione',     'Ibrahima',    'Chef Coffreur'],
            ['Sylla',      'Mamadou',     'Coffreur'],
            ['Faye',       'Ousmane',     'Coffreur'],
            ['Diop',       'Abdou',       'Coffreur'],
            ['Gueye',      'Alioune',     'Coffreur'],
            ['Sarr',       'Lamine',      'Coffreur'],

            ['Diallo',     'Omar',        'Chef Ferrailleur'],
            ['Ndiaye',     'Oumar',       'Ferrailleur'],
            ['Mbaye',      'Cheikh',      'Ferrailleur'],
            ['Seck',       'Moustapha',   'Ferrailleur'],
            ['Ka',         'Mamadou',     'Ferrailleur'],

            ['Camara',     'Seydou',      'Chef Électricien'],
            ['Diallo',     'Amadou',      'Électricien'],
            ['Barry',      'Moussa',      'Électricien'],
            ['Bah',        'Ibrahima',    'Électricien'],

            ['Lo',         'Abdoulaye',   'Grutier'],

            ['Badiane',    'Yankhoba',    'Manœuvre'],
            ['Diatta',     'Assane',      'Manœuvre'],
            ['Diouf',      'Cheikh',      'Manœuvre'],
            ['Ndao',       'Papa',        'Manœuvre'],
            ['Sagna',      'Aliou',       'Manœuvre'],
            ['Coly',       'Moussa',      'Manœuvre'],
            ['Sané',       'Ousmane',     'Manœuvre'],
            ['Bâ',         'Abdou',       'Manœuvre'],
            ['Camara',     'Lamine',      'Manœuvre'],
            ['Fall',       'Ibrahima',    'Manœuvre'],
        ];

        $compteurTel = 770000001;

        foreach ($personnel1 as [$nom, $prenom, $poste]) {
            if (isset($postes[$poste])) {
                Ouvrier::create([
                    'nomOuvrier'    => $nom,
                    'prenomOuvrier' => $prenom,
                    'telOuvrier'    => (string) $compteurTel++,
                    'statutOuvrier' => 'actif',
                    'poste_id'      => $postes[$poste],
                    'chantier_id'   => $chantier1->id,
                ]);
            }
        }

        // Personnel du chantier 2 (Al Makhtoum) — 10 ouvriers
        $personnel2 = [
            ['Kane',   'Ousmane',  'Pointeur'],
            ['Ba',     'Ousmane',  'Chef Maçon'],
            ['Dieng',  'Serigne',  'Maçon'],
            ['Faye',   'Landing',  'Maçon'],
            ['Mbaye',  'Cheikh',   'Coffreur'],
            ['Deme',   'Moussa',   'Ferrailleur'],
            ['Toure',  'Abdou',    'Manœuvre'],
            ['Sy',     'Modou',    'Manœuvre'],
            ['Diallo', 'Seydou',   'Électricien'],
            ['Niang',  'Assane',   'Peintre'],
        ];

        foreach ($personnel2 as [$nom, $prenom, $poste]) {
            if (isset($postes[$poste])) {
                Ouvrier::create([
                    'nomOuvrier'    => $nom,
                    'prenomOuvrier' => $prenom,
                    'telOuvrier'    => (string) $compteurTel++,
                    'statutOuvrier' => 'actif',
                    'poste_id'      => $postes[$poste],
                    'chantier_id'   => $chantier2->id,
                ]);
            }
        }
    }
}
