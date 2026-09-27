<?php

namespace App\Http\Controllers\Api\Pointeur;

use App\Helpers\SemaineHelper;
use App\Http\Controllers\Controller;
use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Pointage;
use App\Models\RecapHebdomadaire;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $chantier = Chantier::where('pointeur_id', auth()->id())->first();

        if (!$chantier) {
            return response()->json(['chantier' => null]);
        }

        $today = Carbon::today();
        $semaine = $today->isoWeek();
        $annee = $today->year;

        $pointagesAujourdhui = Pointage::where('chantier_id', $chantier->id)->whereDate('date', $today)->get();

        $ficheJour = [
            'enregistree' => $pointagesAujourdhui->isNotEmpty(),
            'presents' => $pointagesAujourdhui->where('statutPointage', 'present')->count(),
            'absents' => $pointagesAujourdhui->where('statutPointage', 'absent')->count(),
            'total' => Ouvrier::where('chantier_id', $chantier->id)->where('statutOuvrier', 'actif')->count(),
        ];

        $recapStatut = RecapHebdomadaire::where('chantier_id', $chantier->id)->where('semaine', $semaine)->where('annee', $annee)->first();

        // ── Détection de TOUTE semaine rejetée non encore corrigée (pas seulement celle en cours) ──
        $semainesRejetees = RecapHebdomadaire::where('chantier_id', $chantier->id)
            ->where('statutRecap', 'rejetee')
            ->select('semaine', 'annee', 'motif_rejet')
            ->distinct()
            ->orderByDesc('annee')->orderByDesc('semaine')
            ->get()
            ->map(fn($r) => [
                'semaine' => $r->semaine,
                'annee' => $r->annee,
                'motif_rejet' => $r->motif_rejet,
                'label' => SemaineHelper::libelle($r->semaine, $r->annee),
            ]);

        $livraisons = Approvisionnement::with('bonReceptions')
            ->where('chantier_id', $chantier->id)
            ->whereIn('statutAppro', ['en_cours_livraison', 'partiellement_recue'])
            ->orderByRaw("CASE priorite WHEN 'urgent' THEN 1 ELSE 2 END")
            ->take(5)->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'designation' => $l->designation,
                'unite' => $l->unite,
                'priorite' => $l->priorite,
                'quantite_demandee' => (float) $l->quantite_demandee,
                'quantite_restante' => $l->quantite_restante,
            ]);

        return response()->json([
            'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier, 'localisation' => $chantier->localisation, 'statut' => $chantier->statut],
            'fiche_jour' => $ficheJour,
            'recap' => ['statut' => $recapStatut?->statutRecap ?? 'en_attente', 'motif_rejet' => $recapStatut?->motif_rejet],
            'semaines_rejetees' => $semainesRejetees,
            'livraisons' => $livraisons,
            'semaine' => $semaine,
            'annee' => $annee,
            'today' => $today->toDateString(),
        ]);
    }
}
