<?php

namespace App\Http\Controllers\Api\Pointeur;

use App\Helpers\ApprovisionnementHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approvisionnement\ReceptionRequest;
use App\Http\Resources\ApprovisionnementResource;
use App\Http\Resources\BonReceptionResource;
use App\Models\Approvisionnement;
use App\Models\BonReception;
use App\Models\Chantier;
use App\Services\ApprovisionnementService;
use Illuminate\Http\Request;

class ReceptionController extends Controller
{
    public function __construct(private ApprovisionnementService $approService) {}

    public function livraisons()
    {
        $chantier = Chantier::where('pointeur_id', auth()->id())->firstOrFail();

        $livraisons = Approvisionnement::with(['demandeur', 'bonReceptions'])
            ->where('chantier_id', $chantier->id)
            ->whereIn('statutAppro', ['en_cours_livraison', 'partiellement_recue'])
            ->orderByRaw("FIELD(priorite, 'urgent', 'normal')")
            ->get();

        return response()->json([
            'chantier'   => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
            'livraisons' => ApprovisionnementResource::collection($livraisons),
        ]);
    }

    public function validerReception(ReceptionRequest $request, Approvisionnement $demande)
    {
        $chantier = Chantier::where('pointeur_id', auth()->id())->where('id', $demande->chantier_id)->first();
        abort_if(!$chantier, 403, "Vous n'êtes pas responsable du chantier de cette demande.");

        try {
            $bon = $this->approService->receptionner($demande, $request->validated(), auth()->id());

            return response()->json([
                'message' => "Réception enregistrée. Bon de réception généré.",
                'bon'     => new BonReceptionResource($bon->load(['demande', 'receptionneePar'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function bonReceptionPdf(BonReception $bon)
    {
        $chantier = Chantier::where('pointeur_id', auth()->id())->first();
        abort_if(!$chantier || $bon->chantier_id !== $chantier->id, 403);

        return $this->approService->genererBonReceptionPdf($bon);
    }

    public function historiqueLivraisons(Request $request)
    {
        $chantier = Chantier::where('pointeur_id', auth()->id())->firstOrFail();

        $filtres = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $dateDebut = $filtres['date_debut'] ?? now()->startOfMonth()->toDateString();
        $dateFin   = $filtres['date_fin'] ?? now()->toDateString();

        $bons = BonReception::with(['demande', 'receptionneePar'])
            ->where('chantier_id', $chantier->id)
            ->whereBetween('date_reception', [$dateDebut, $dateFin])
            ->orderByDesc('date_reception')
            ->get();

        return response()->json([
            'bons' => BonReceptionResource::collection($bons),
            'stats' => [
                'total'      => $bons->count(),
                'completes'  => $bons->filter(fn($b) => ApprovisionnementHelper::quantiteRestante($b->demande) <= 0)->count(),
                'partielles' => $bons->filter(fn($b) => ApprovisionnementHelper::quantiteRestante($b->demande) > 0)->count(),
            ],
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
        ]);
    }
}
