<?php

namespace Database\Seeders;

use App\Models\Chantier;
use App\Models\RapportChantier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RapportChantierSeeder extends Seeder
{
    public function run(): void
    {
        $chantier3M         = Chantier::where('nomChantier', '3M')->first();
        $chantierAlMakhtoum = Chantier::where('nomChantier', 'Al Makhtoum')->first();
        $chefProjet         = User::where('email', 'chefprojet@dimagroupe.com')->first();

        if (!$chantier3M || !$chantierAlMakhtoum || !$chefProjet) {
            $this->command->error("Les chantiers '3M'/'Al Makhtoum' et le chef de projet doivent exister avant ce seeder.");
            return;
        }

        $rapports3M = [
            ['type' => 'avancement', 'titre' => "Avancement hebdomadaire — Fondations",       'contenu' => "La coulée des fondations est terminée à 100 %. Aucun incident notable cette semaine. L'équipe passe à l'élévation dès lundi.", 'jours' => 21],
            ['type' => 'reunion',    'titre' => "Réunion de chantier — Point mensuel",         'contenu' => "Réunion avec les sous-traitants pour valider le planning d'élévation. Décision de renforcer l'équipe de coffreurs à partir de la semaine prochaine.", 'jours' => 14],
            ['type' => 'incident',   'titre' => "Retard livraison ferraillage",                'contenu' => "Le fournisseur a signalé un retard de 3 jours sur la livraison du fer à béton 12mm, impactant potentiellement le planning de l'élévation.", 'jours' => 7],
            ['type' => 'avancement', 'titre' => "Avancement hebdomadaire — Élévation",         'contenu' => "L'élévation des murs porteurs est à 50 % d'avancement. Le rythme est conforme au planning prévisionnel.", 'jours' => 2],
        ];

        $rapportsAlMakhtoum = [
            ['type' => 'livraison',  'titre' => "Réception ciment et sable",                   'contenu' => "Réception complète de la commande de ciment CPA 42.5. Stockage effectué sur site sans anomalie.", 'jours' => 18],
            ['type' => 'avancement', 'titre' => "Avancement hebdomadaire — Second œuvre",       'contenu' => "Les travaux de second œuvre progressent normalement. L'équipe électricité a démarré le tirage des câbles au rez-de-chaussée.", 'jours' => 10],
            ['type' => 'incident',   'titre' => "Panne de la grue",                            'contenu' => "Panne technique de la grue de chantier, résolue en une demi-journée par le prestataire de maintenance. Impact limité sur le planning.", 'jours' => 5],
            ['type' => 'autre',      'titre' => "Visite du DAF",                                'contenu' => "Visite de contrôle budgétaire sur site. Aucune anomalie relevée sur le suivi des dépenses matériaux.", 'jours' => 1],
        ];

        foreach ([$chantier3M->id => $rapports3M, $chantierAlMakhtoum->id => $rapportsAlMakhtoum] as $chantierId => $rapports) {
            foreach ($rapports as $r) {
                RapportChantier::create([
                    'date_rapport' => Carbon::now()->subDays($r['jours'])->toDateString(),
                    'titre'        => $r['titre'],
                    'type'         => $r['type'],
                    'contenu'      => $r['contenu'],
                    'chantier_id'  => $chantierId,
                    'auteur_id'    => $chefProjet->id,
                ]);
            }
        }
    }
}
