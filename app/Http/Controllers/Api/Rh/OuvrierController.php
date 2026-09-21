<?php

namespace App\Http\Controllers\Api\Rh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ouvrier\OuvrierRequest;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Poste;
use App\Services\OuvrierService;
use Illuminate\Http\Request;

class OuvrierController extends Controller
{
    public function __construct(private OuvrierService $personnelService) {}

    public function index(Request $request)
    {
        $query = Ouvrier::with(['poste', 'chantier'])->orderBy('nomOuvrier');

        if ($request->filled('chantier_id')) {
            $query->where('chantier_id', $request->input('chantier_id'));
        }

        if ($request->input('statut', 'tous') !== 'tous') {
            $query->where('statutOuvrier', $request->input('statut'));
        }

        if ($request->filled('recherche')) {
            $r = $request->input('recherche');
            $query->where(fn($q) => $q->where('nomOuvrier', 'like', "%{$r}%")->orWhere('prenomOuvrier', 'like', "%{$r}%"));
        }

        $personnel = $query->paginate(10)->withQueryString();

        $agregats = Ouvrier::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN statutOuvrier = 'actif' THEN 1 ELSE 0 END) as actifs,
            SUM(CASE WHEN statutOuvrier = 'inactif' THEN 1 ELSE 0 END) as inactifs
        ")->first();

        return response()->json([
            'personnel' => $personnel,
            'stats' => ['total' => (int) $agregats->total, 'actifs' => (int) $agregats->actifs, 'inactifs' => (int) $agregats->inactifs],
        ]);
    }

    public function formOptions()
    {
        return response()->json([
            'postes'    => Poste::orderBy('libelle')->get(),
            'chantiers' => Chantier::whereIn('statut', ['en_attente', 'en_cours'])->orderBy('nomChantier')->get(['id', 'nomChantier']),
        ]);
    }

    public function store(OuvrierRequest $request)
    {
        $ouvrier = $this->personnelService->creer($request->validated());
        return response()->json(['message' => 'Ouvrier ajouté avec succès.', 'ouvrier' => $ouvrier->load(['poste', 'chantier'])], 201);
    }

    public function update(OuvrierRequest $request, Ouvrier $ouvrier)
    {
        $ouvrier = $this->personnelService->modifier($ouvrier, $request->validated());
        return response()->json(['message' => 'Ouvrier mis à jour avec succès.', 'ouvrier' => $ouvrier->load(['poste', 'chantier'])]);
    }

    public function toggleStatut(Ouvrier $ouvrier)
    {
        $ouvrier = $this->personnelService->toggleStatut($ouvrier);
        return response()->json([
            'message' => $ouvrier->statutOuvrier === 'actif' ? 'Ouvrier activé.' : 'Ouvrier désactivé.',
            'ouvrier' => $ouvrier->load(['poste', 'chantier']),
        ]);
    }

    public function destroy(Ouvrier $ouvrier)
    {
        try {
            $this->personnelService->supprimer($ouvrier);
            return response()->json(['message' => 'Ouvrier supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
