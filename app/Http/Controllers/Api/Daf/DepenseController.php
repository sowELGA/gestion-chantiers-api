<?php

namespace App\Http\Controllers\Api\Daf;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chantier\DepenseRequest;
use App\Models\Chantier;
use App\Models\DepensesChantier;
use App\Services\DepenseService;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    public function __construct(private DepenseService $depenseService) {}

    public function index(Request $request)
    {
        $statutFiltre = $request->input('statut', 'en_cours');

        $query = Chantier::withSum('depenses', 'montant')->withCount('depenses');

        if ($statutFiltre !== 'toutes') {
            $query->where('statut', $statutFiltre);
        }

        $chantiers = $query
            ->orderByRaw("FIELD(statut, 'en_cours', 'en_attente', 'suspendu', 'livre')")
            ->orderBy('nomChantier')
            ->get(['id', 'nomChantier', 'localisation', 'statut', 'budget_prevu']);

        return response()->json([
            'chantiers' => $chantiers,
            'statutFiltre' => $statutFiltre,
        ]);
    }

    public function show(Request $request, Chantier $chantier)
    {
        $filtres = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $dateDebut = $filtres['date_debut'] ?? now()->startOfMonth()->toDateString();
        $dateFin   = $filtres['date_fin'] ?? now()->toDateString();

        $depenses = DepensesChantier::where('chantier_id', $chantier->id)
            ->whereBetween('date_depense', [$dateDebut, $dateFin])
            ->orderByDesc('date_depense')
            ->get();

        return response()->json([
            'chantier' => [
                'id' => $chantier->id,
                'nomChantier' => $chantier->nomChantier,
                'statut' => $chantier->statut,
                'budget_prevu' => $chantier->budget_prevu
            ],
            'depenses' => $depenses,
            'stats' => [
                'total'         => (float) $depenses->sum('montant'),
                'nb'            => $depenses->count(),
                'par_categorie' => $depenses->groupBy('categorie')->map(fn($g) => (float) $g->sum('montant')),
                'total_global'  => (float) DepensesChantier::where('chantier_id', $chantier->id)->sum('montant'),
            ],
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
        ]);
    }

    public function store(DepenseRequest $request, Chantier $chantier)
    {
        if ($chantier->statut === 'livre') {
            return response()->json(['message' => 'Ce chantier est livré — les dépenses sont en lecture seule.'], 403);
        }

        $depense = $this->depenseService->ajouter($chantier, $request->validated());
        return response()->json(['message' => 'Dépense ajoutée avec succès.', 'depense' => $depense], 201);
    }

    public function destroy(DepensesChantier $depense)
    {
        if ($depense->chantier->statut === 'livre') {
            return response()->json(['message' => 'Ce chantier est livré — les dépenses sont en lecture seule.'], 403);
        }

        $this->depenseService->supprimer($depense);
        return response()->json(['message' => 'Dépense supprimée avec succès.']);
    }
}
