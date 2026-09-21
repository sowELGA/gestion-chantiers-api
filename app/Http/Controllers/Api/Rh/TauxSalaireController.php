<?php

namespace App\Http\Controllers\Api\Rh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ouvrier\TauxSalaireRequest;
use App\Models\Chantier;
use App\Services\TauxSalaireService;

class TauxSalaireController extends Controller
{
    public function __construct(private TauxSalaireService $tauxSalaireService) {}

    public function index()
    {
        $chantiers = Chantier::whereIn('statut', ['en_attente', 'en_cours'])
            ->withCount('tauxSalaires')
            ->orderBy('nomChantier')
            ->get(['id', 'nomChantier']);

        return response()->json($chantiers);
    }

    public function show(Chantier $chantier)
    {
        return response()->json([
            'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
            'matrice'  => $this->tauxSalaireService->getMatriceTaux($chantier->id),
        ]);
    }

    public function update(TauxSalaireRequest $request, Chantier $chantier)
    {
        $this->tauxSalaireService->enregistrerTaux($chantier->id, $request->taux);
        return response()->json(['message' => 'Taux salariaux enregistrés avec succès.']);
    }
}
