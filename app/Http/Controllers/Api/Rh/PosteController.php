<?php

namespace App\Http\Controllers\Api\Rh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ouvrier\PosteRequest;
use App\Models\Poste;
use App\Services\PosteService;

class PosteController extends Controller
{
    public function __construct(private PosteService $posteService) {}

    public function index()
    {
        return response()->json(Poste::withCount('ouvriers')->orderBy('libelle')->get());
    }

    public function store(PosteRequest $request)
    {
        $poste = $this->posteService->creer($request->validated());
        return response()->json(['message' => 'Poste créé avec succès.', 'poste' => $poste], 201);
    }

    public function update(PosteRequest $request, Poste $poste)
    {
        $poste = $this->posteService->modifier($poste, $request->validated());
        return response()->json(['message' => 'Poste mis à jour avec succès.', 'poste' => $poste]);
    }

    public function destroy(Poste $poste)
    {
        try {
            $this->posteService->supprimer($poste);
            return response()->json(['message' => 'Poste supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
