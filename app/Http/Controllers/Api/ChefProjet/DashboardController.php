<?php

namespace App\Http\Controllers\Api\ChefProjet;

use App\Http\Controllers\Controller;
use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Models\RecapHebdomadaire;
use App\Models\Tache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $mesChantiers = Chantier::with(['phases', 'taches'])
            ->where('chef_projet_id', $userId)
            ->whereIn('statut', ['en_cours', 'en_attente', 'suspendu'])
            ->get();

        $semaine = Carbon::today()->isoWeek();
        $annee = Carbon::today()->year;

        $kpi = [
            'mes_chantiers' => $mesChantiers->count(),
            'taches_en_cours' => Tache::whereHas('chantier', fn($q) => $q->where('chef_projet_id', $userId))
                ->where('statutTache', 'en_cours')->count(),
            'taches_en_retard' => Tache::whereHas('chantier', fn($q) => $q->where('chef_projet_id', $userId))
                ->where('statutTache', '!=', 'terminee')->where('date_fin_prevue', '<', now())->count(),
            'fiches_a_valider' => RecapHebdomadaire::whereHas('chantier', fn($q) => $q->where('chef_projet_id', $userId))
                ->where('statutRecap', 'soumise')->where('semaine', $semaine)->where('annee', $annee)
                ->distinct('chantier_id')->count('chantier_id'),
            'demandes_attente' => Approvisionnement::whereHas('chantier', fn($q) => $q->where('chef_projet_id', $userId))
                ->where('statutAppro', 'en_attente')->count(),
        ];

        $chantiersAvancement = $mesChantiers->map(function ($chantier) {
            $avancement = $chantier->phases->isEmpty() ? 0 : round($chantier->phases->avg('avancement'));
            $enRetard = $chantier->taches->where('statutTache', '!=', 'terminee')
                ->filter(fn($t) => $t->date_fin_prevue && $t->date_fin_prevue->isPast())->count();

            return [
                'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
                'avancement' => $avancement,
                'en_retard' => $enRetard,
            ];
        })->values();

        $tachesEnRetard = Tache::with(['chantier', 'phase'])
            ->whereHas('chantier', fn($q) => $q->where('chef_projet_id', $userId))
            ->where('statutTache', '!=', 'terminee')->where('date_fin_prevue', '<', now())
            ->orderBy('date_fin_prevue')->take(6)->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'nomTache' => $t->nomTache,
                'avancement' => $t->avancement,
                'date_fin_prevue' => $t->date_fin_prevue->format('Y-m-d'),
                'chantier' => ['nomChantier' => $t->chantier->nomChantier],
                'phase' => ['nomPhase' => $t->phase->nomPhase],
            ]);

        $fichesSoumises = RecapHebdomadaire::with('chantier')
            ->whereHas('chantier', fn($q) => $q->where('chef_projet_id', $userId))
            ->where('statutRecap', 'soumise')->where('semaine', $semaine)->where('annee', $annee)
            ->get()->groupBy('chantier_id')
            ->map(fn($fiches, $chantierId) => [
                'chantier_id' => (int) $chantierId,
                'nom_chantier' => $fiches->first()->chantier->nomChantier,
                'nb_ouvriers' => $fiches->count(),
            ])->values();

        return response()->json([
            'kpi' => $kpi,
            'chantiers_avancement' => $chantiersAvancement,
            'taches_en_retard' => $tachesEnRetard,
            'fiches_soumises' => $fichesSoumises,
            'semaine' => $semaine,
            'annee' => $annee,
        ]);
    }
}
