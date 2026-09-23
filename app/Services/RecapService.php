<?php

namespace App\Services;

use App\Helpers\PointageHelper;
use App\Helpers\SemaineHelper;
use App\Models\RecapHebdomadaire;
use Carbon\Carbon;

class RecapService
{
    public function recalculerRecap(int $chantierId, int $semaine, int $annee): void
    {
        $personnel = PointageHelper::personnelActif($chantierId);

        foreach ($personnel as $ouvrier) {
            RecapHebdomadaire::firstOrCreate(
                ['ouvrier_id' => $ouvrier->id, 'chantier_id' => $chantierId, 'semaine' => $semaine, 'annee' => $annee],
                ['statutRecap' => 'en_attente']
            );
        }
    }

    public function getInfosSemaine(int $semaine, int $annee): array
    {
        return [
            'semaine' => $semaine,
            'annee' => $annee,
            'debut' => SemaineHelper::debutDepuisNumero($semaine, $annee),
            'fin' => SemaineHelper::finDepuisNumero($semaine, $annee),
            'jours' => SemaineHelper::jours($semaine, $annee),
        ];
    }

    public function getStatutSemaine(int $chantierId, int $semaine, int $annee): array
    {
        $recaps = PointageHelper::recapsSemaine($chantierId, $semaine, $annee);
        return ['statut' => $recaps->first()?->statutRecap ?? 'en_attente', 'motif_rejet' => $recaps->first()?->motif_rejet];
    }

    public function getRecapComplet(int $chantierId, int $semaine, int $annee, int $page, int $parPage = 15): array
    {
        $tousPersonnel = PointageHelper::personnelActif($chantierId);
        $jours = SemaineHelper::jours($semaine, $annee);
        $pointages = PointageHelper::pointagesSemaine($chantierId, $semaine, $annee);

        // Toutes les lignes (pour les totaux), une seule fois
        $toutesLignes = $tousPersonnel->map(fn($o) => PointageHelper::construireLigneOuvrier($o, $jours, $pointages, $chantierId, $semaine, $annee));

        $total = $tousPersonnel->count();
        $pg = PointageHelper::paginer($total, $page, $parPage);

        // Sous-ensemble paginé, réutilisé depuis les lignes déjà calculées (pas de recalcul)
        $lignesPage = $toutesLignes->forPage($pg['page'], $parPage)->values();

        $totauxParJour = collect($jours)->map(fn($jour, $i) => $toutesLignes->sum(fn($l) => $l['jours'][$i]['statut'] === 'present' ? 1 : 0));

        return [
            'lignes' => $lignesPage,
            'pagination' => $pg,
            'totaux' => [
                'total_presents' => $toutesLignes->sum('jours_present'),
                'total_h_sup' => $toutesLignes->sum('total_h_sup'),
                'totaux_par_jour' => $totauxParJour,
            ],
        ];
    }

    public function soumettreSemaine(int $chantierId, int $pointeurId, int $semaine, int $annee): void
    {
        $this->recalculerRecap($chantierId, $semaine, $annee);

        RecapHebdomadaire::where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)
            ->whereIn('statutRecap', ['en_attente', 'rejetee'])
            ->update(['statutRecap' => 'soumise', 'soumis_par_id' => $pointeurId, 'motif_rejet' => null]);
    }

    public function validerSemaine(int $chantierId, int $semaine, int $annee, int $chefId): void
    {
        RecapHebdomadaire::where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)
            ->where('statutRecap', 'soumise')
            ->update(['statutRecap' => 'validee_cp', 'valide_par_id' => $chefId, 'valide_le' => now(), 'motif_rejet' => null]);
    }

    public function rejeterSemaine(int $chantierId, int $semaine, int $annee, int $chefId, string $motif): void
    {
        RecapHebdomadaire::where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)
            ->where('statutRecap', 'soumise')
            ->update(['statutRecap' => 'rejetee', 'motif_rejet' => $motif, 'valide_par_id' => $chefId, 'valide_le' => now()]);
    }

    public function getSemainesDisponibles(int $nbSemaines = 12): \Illuminate\Support\Collection
    {
        return collect(range(0, $nbSemaines - 1))->map(function ($i) {
            $date = Carbon::today()->subDays($i * 7);
            $semaine = SemaineHelper::numeroCycle($date);
            $annee = SemaineHelper::anneeCycle($date);
            return ['semaine' => $semaine, 'annee' => $annee, 'label' => SemaineHelper::libelle($semaine, $annee)];
        })->unique(fn($s) => $s['semaine'] . '-' . $s['annee'])->values();
    }

    public function calculerSalaires(int $chantierId, int $semaine, int $annee): void
    {
        RecapHebdomadaire::where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)
            ->update(['statutRecap' => 'envoyee_direction']);
    }
}
