<?php

namespace App\Http\Controllers\Api\ChefProjet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Approvisionnement\ApprovisionnementRequest;
use App\Http\Resources\ApprovisionnementResource;
use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Services\ApprovisionnementService;
use Illuminate\Http\Request;

class ApprovisionnementController extends Controller
{
    public function __construct(private ApprovisionnementService $approService) {}

    public function chantiersDisponibles()
    {
        $chantiers = Chantier::where('chef_projet_id', auth()->id())
            ->whereIn('statut', ['en_cours', 'en_attente'])
            ->orderBy('nomChantier')
            ->get(['id', 'nomChantier']);

        return response()->json($chantiers);
    }

    public function index(Request $request)
    {
        $filtres = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
            'statut'     => 'nullable|string',
        ]);

        $dateDebut = $filtres['date_debut'] ?? now()->startOfMonth()->toDateString();
        $dateFin   = $filtres['date_fin'] ?? now()->toDateString();
        $statut    = $filtres['statut'] ?? 'tous';

        $demandes = Approvisionnement::with('chantier')
            ->whereHas('chantier', fn($q) => $q->where('chef_projet_id', auth()->id()))
            ->whereBetween('created_at', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
            ->when($statut !== 'tous', fn($q) => $q->where('statutAppro', $statut))
            ->orderByRaw("FIELD(priorite, 'urgent', 'normal')")
            ->orderByDesc('created_at')
            ->get();

        $brut = Approvisionnement::whereHas('chantier', fn($q) => $q->where('chef_projet_id', auth()->id()))
            ->selectRaw("
                SUM(CASE WHEN statutAppro = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN statutAppro IN ('validee','en_cours_livraison','partiellement_recue') THEN 1 ELSE 0 END) as en_cours_livraison,
                SUM(CASE WHEN statutAppro = 'cloturee' THEN 1 ELSE 0 END) as cloturee,
                SUM(CASE WHEN statutAppro = 'rejetee' THEN 1 ELSE 0 END) as rejetee
            ")->first();

        return response()->json([
            'demandes' => ApprovisionnementResource::collection($demandes),
            'stats' => [
                'en_attente'         => (int) $brut->en_attente,
                'en_cours_livraison' => (int) $brut->en_cours_livraison,
                'cloturee'           => (int) $brut->cloturee,
                'rejetee'            => (int) $brut->rejetee,
            ],
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
            'statut'    => $statut,
        ]);
    }

    public function store(ApprovisionnementRequest $request)
    {
        $this->approService->creerPlusieurs($request->validated(), auth()->id());
        return response()->json(['message' => "Demandes d'approvisionnement enregistrées avec succès."], 201);
    }

    public function update(ApprovisionnementRequest $request, Approvisionnement $demande)
    {
        abort_if($demande->chantier->chef_projet_id !== auth()->id(), 403);

        try {
            $data = $request->validated()['demandes'][0] ?? $request->validated();
            $demande = $this->approService->modifier($demande, array_merge($data, ['chantier_id' => $request->input('chantier_id')]));
            return response()->json(['message' => 'Demande modifiée avec succès.', 'demande' => new ApprovisionnementResource($demande)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Approvisionnement $demande)
    {
        abort_if($demande->chantier->chef_projet_id !== auth()->id(), 403);

        try {
            $this->approService->supprimer($demande);
            return response()->json(['message' => 'Demande supprimée.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
