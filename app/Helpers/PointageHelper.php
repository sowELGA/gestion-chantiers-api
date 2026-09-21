<?php

namespace App\Helpers;

use App\Models\Ouvrier;
use App\Models\Pointage;
use App\Models\Poste;
use App\Models\RecapHebdomadaire;
use App\Models\TauxSalaire;
use Carbon\Carbon;

class PointageHelper
{
    public static function personnelActif(int $chantierId): \Illuminate\Support\Collection
    {
        return Ouvrier::with('poste')
            ->join('postes', 'ouvriers.poste_id', '=', 'postes.id')
            ->where('ouvriers.chantier_id', $chantierId)
            ->where('ouvriers.statutOuvrier', 'actif')
            ->orderByRaw("TRIM(REPLACE(LOWER(postes.libelle), 'chef ', ''))")
            ->orderByRaw("CASE WHEN LOWER(postes.libelle) LIKE 'chef %' THEN 0 ELSE 1 END")
            ->orderBy('ouvriers.nomOuvrier')
            ->select('ouvriers.*')
            ->get();
    }

    public static function paginer(int $total, int $page, int $parPage): array
    {
        $pages = max(1, (int) ceil($total / $parPage));
        $page = max(1, min($page, $pages));

        return [
            'page' => $page,
            'parPage' => $parPage,
            'total' => $total,
            'pages' => $pages,
            'debut' => ($page - 1) * $parPage + 1,
            'fin' => min($page * $parPage, $total),
        ];
    }

    public static function pointagesSemaine(int $chantierId, int $semaine, int $annee): \Illuminate\Support\Collection
    {
        $samedi = SemaineHelper::debutDepuisNumero($semaine, $annee);
        $vendredi = SemaineHelper::finDepuisNumero($semaine, $annee);

        return Pointage::with('poste')
            ->where('chantier_id', $chantierId)
            ->whereBetween('date', [$samedi->toDateString(), $vendredi->toDateString()])
            ->get()
            ->groupBy('ouvrier_id');
    }

    public static function recapsSemaine(int $chantierId, int $semaine, int $annee): \Illuminate\Support\Collection
    {
        return RecapHebdomadaire::where('chantier_id', $chantierId)
            ->where('semaine', $semaine)->where('annee', $annee)
            ->get()->keyBy('ouvrier_id');
    }

    public static function snapshotTaux(Ouvrier $ouvrier, int $chantierId): array
    {
        $taux = TauxSalaire::where('poste_id', $ouvrier->poste_id)->where('chantier_id', $chantierId)->first();

        return [
            'poste_id' => $ouvrier->poste_id,
            'taux_journalier' => $taux->taux_journalier ?? null,
            'taux_heure_sup' => $taux->taux_heure_sup ?? null,
        ];
    }

    public static function posteDepuisPointages(Ouvrier $ouvrier, \Illuminate\Support\Collection $pointagesOuvrier): ?Poste
    {
        $poste = $pointagesOuvrier->sortKeys()->pluck('poste')->filter()->first();
        return $poste ?: $ouvrier->poste;
    }

    public static function calculerSalaireDepuisPointages(\Illuminate\Support\Collection $pointages): array
    {
        $presents = $pointages->where('statutPointage', 'present');

        $joursPresents = $presents->count();
        $totalHeuresSup = (int) $pointages->sum('heures_sup');

        $salaireBase = (float) $presents->sum(fn($p) => (float) ($p->taux_journalier ?? 0));
        $salaireHeuresSup = (float) $pointages->sum(fn($p) => (int) $p->heures_sup * (float) ($p->taux_heure_sup ?? 0));

        return [
            'jours_presents' => $joursPresents,
            'total_heures_sup' => $totalHeuresSup,
            'salaire_base' => $salaireBase,
            'salaire_heures_sup' => $salaireHeuresSup,
            'salaire_total' => $salaireBase + $salaireHeuresSup,
        ];
    }

    public static function construireLigneOuvrier(Ouvrier $ouvrier, array $jours, \Illuminate\Support\Collection $pointages, int $chantierId, int $semaine, int $annee): array
    {
        $pointagesOuvrier = $pointages->get($ouvrier->id, collect())->keyBy(fn($p) => Carbon::parse($p->date)->toDateString());

        $joursDetails = collect($jours)->map(fn($jour) => [
            'date' => $jour->toDateString(),
            'statut' => $pointagesOuvrier->get($jour->toDateString())?->statutPointage ?? null,
            'h_sup' => (int) ($pointagesOuvrier->get($jour->toDateString())?->heures_sup ?? 0),
        ]);

        $salaire = self::calculerSalaireDepuisPointages($pointagesOuvrier->values());
        $poste = self::posteDepuisPointages($ouvrier, $pointagesOuvrier);

        return [
            'ouvrier' => ['id' => $ouvrier->id, 'nomComplet' => $ouvrier->nom_complet],
            'poste' => $poste?->libelle,
            'jours' => $joursDetails,
            'jours_present' => $joursDetails->where('statut', 'present')->count(),
            'total_h_sup' => $joursDetails->sum('h_sup'),
            'salaire_base' => $salaire['salaire_base'],
            'salaire_h_sup' => $salaire['salaire_heures_sup'],
            'salaire_total' => $salaire['salaire_total'],
        ];
    }
}
