<?php

namespace App\Http\Controllers\Api\Daf;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovisionnementResource;
use App\Models\Approvisionnement;
use App\Services\ApprovisionnementService;
use Illuminate\Http\Request;

class ApprovisionnementController extends Controller
{
    public function __construct(private ApprovisionnementService $approService) {}

    public function index()
    {
        $demandesEnAttente = Approvisionnement::with(['chantier', 'demandeur'])
            ->where('statutAppro', 'en_attente')
            ->orderByRaw("FIELD(priorite, 'urgent', 'normal')")
            ->orderBy('created_at')
            ->get();

        $demandesEnCours = Approvisionnement::with(['chantier', 'demandeur'])
            ->whereIn('statutAppro', ['validee', 'en_cours_livraison', 'partiellement_recue'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'demandesEnAttente' => ApprovisionnementResource::collection($demandesEnAttente),
            'demandesEnCours'   => ApprovisionnementResource::collection($demandesEnCours),
        ]);
    }

    public function historique(Request $request)
    {
        $filtres = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $dateDebut = $filtres['date_debut'] ?? now()->startOfMonth()->toDateString();
        $dateFin   = $filtres['date_fin'] ?? now()->toDateString();

        $demandes = Approvisionnement::with(['chantier', 'demandeur'])
            ->whereIn('statutAppro', ['cloturee', 'rejetee'])
            ->whereBetween('updated_at', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        return response()->json([
            'demandes'  => ApprovisionnementResource::collection($demandes),
            'stats'     => [
                'cloturees' => $demandes->where('statutAppro', 'cloturee')->count(),
                'rejetees'  => $demandes->where('statutAppro', 'rejetee')->count(),
                'total'     => $demandes->count(),
            ],
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
        ]);
    }

    public function valider(Approvisionnement $demande)
    {
        try {
            $demande = $this->approService->valider($demande);
            return response()->json(['message' => 'Demande validée avec succès.', 'demande' => new ApprovisionnementResource($demande)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function rejeter(Approvisionnement $demande)
    {
        try {
            $demande = $this->approService->rejeter($demande);
            return response()->json(['message' => 'Demande rejetée.', 'demande' => new ApprovisionnementResource($demande)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function passerCommande(Request $request, Approvisionnement $demande)
    {
        $data = $request->validate(['date_livraison_prevue' => 'nullable|date|after_or_equal:today']);

        try {
            $demande = $this->approService->commander($demande, $data['date_livraison_prevue'] ?? null);
            return response()->json(['message' => 'Commande passée — en cours de livraison.', 'demande' => new ApprovisionnementResource($demande)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function definirDateLivraison(Request $request, Approvisionnement $demande)
    {
        $data = $request->validate(['date_livraison_prevue' => 'nullable|date']);

        try {
            $demande = $this->approService->definirDateLivraisonPrevue($demande, $data['date_livraison_prevue'] ?? null);
            return response()->json(['message' => 'Date de livraison prévue mise à jour.', 'demande' => new ApprovisionnementResource($demande)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
