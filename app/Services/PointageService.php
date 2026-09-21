<?php

namespace App\Services;

use App\Helpers\PointageHelper;
use App\Helpers\SemaineHelper;
use App\Models\Ouvrier;
use App\Models\Pointage;
use App\Models\RecapHebdomadaire;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PointageService
{
    public function getPointagesDuJour(int $chantierId): array
    {
        $today = Carbon::today();
        $pointages = Pointage::where('chantier_id', $chantierId)->whereDate('date', $today)->get()->keyBy('ouvrier_id');

        return ['date' => $today, 'pointages' => $pointages, 'ficheExiste' => $pointages->isNotEmpty()];
    }

    public function getToutPersonnel(int $chantierId): \Illuminate\Support\Collection
    {
        return PointageHelper::personnelActif($chantierId);
    }

    public function jourModifiable(int $chantierId, Carbon $date): bool
    {
        $semaine = SemaineHelper::numeroCycle($date);
        $annee = SemaineHelper::anneeCycle($date);

        $recaps = RecapHebdomadaire::where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)->get();

        if ($recaps->isEmpty()) return true;

        return $recaps->every(fn($r) => in_array($r->statutRecap, ['en_attente', 'rejetee']));
    }

    public function semaineModifiable(int $chantierId): bool
    {
        return $this->jourModifiable($chantierId, Carbon::today());
    }

    private function sauvegarderLignes(array $lignes, int $chantierId, string $date): void
    {
        $ouvriers = Ouvrier::whereIn('id', array_column($lignes, 'ouvrier_id'))->get()->keyBy('id');

        foreach ($lignes as $ligne) {
            $ouvrier = $ouvriers->get($ligne['ouvrier_id']);
            $estPresent = $ligne['statutPointage'] === 'present';

            $snapshot = $estPresent && $ouvrier
                ? PointageHelper::snapshotTaux($ouvrier, $chantierId)
                : ['poste_id' => $ouvrier?->poste_id, 'taux_journalier' => null, 'taux_heure_sup' => null];

            Pointage::updateOrCreate(
                ['ouvrier_id' => $ligne['ouvrier_id'], 'chantier_id' => $chantierId, 'date' => $date],
                [
                    'statutPointage' => $ligne['statutPointage'],
                    'heures_sup' => $estPresent ? (int) ($ligne['heures_sup'] ?? 0) : 0,
                    'poste_id' => $snapshot['poste_id'],
                    'taux_journalier' => $snapshot['taux_journalier'],
                    'taux_heure_sup' => $snapshot['taux_heure_sup'],
                ]
            );
        }
    }

    public function enregistrerFiche(array $lignes, int $chantierId, RecapService $recapService): void
    {
        $today = Carbon::today();
        $semaine = SemaineHelper::numeroCycle($today);
        $annee = SemaineHelper::anneeCycle($today);

        if (!$this->jourModifiable($chantierId, $today)) {
            throw new \Exception('La fiche de cette semaine a déjà été soumise.');
        }

        DB::transaction(function () use ($lignes, $chantierId, $today, $semaine, $annee, $recapService) {
            $this->sauvegarderLignes($lignes, $chantierId, $today->toDateString());
            $recapService->recalculerRecap($chantierId, $semaine, $annee);
        });
    }

    public function modifierPointageJour(int $chantierId, string $date, array $lignes, RecapService $recapService): void
    {
        $dateCarbon = Carbon::parse($date);
        $semaine = SemaineHelper::numeroCycle($dateCarbon);
        $annee = SemaineHelper::anneeCycle($dateCarbon);

        $recaps = RecapHebdomadaire::where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)->get();

        if ($recaps->isNotEmpty() && !$recaps->every(fn($r) => in_array($r->statutRecap, ['en_attente', 'rejetee']))) {
            throw new \Exception('Ce récap ne peut plus être modifié.');
        }

        DB::transaction(function () use ($lignes, $chantierId, $date, $semaine, $annee, $recapService) {
            $this->sauvegarderLignes($lignes, $chantierId, $date);
            $recapService->recalculerRecap($chantierId, $semaine, $annee);
        });
    }
}
