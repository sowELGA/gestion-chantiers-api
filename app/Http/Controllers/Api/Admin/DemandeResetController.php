<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandeResetMdp;
use App\Services\UserService;

class DemandeResetController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index()
    {
        return DemandeResetMdp::with('user:id,nomUser,prenomUser,email')
            ->enAttente()
            ->latest()
            ->get();
    }

    public function resoudre(DemandeResetMdp $demande)
    {
        $this->userService->resoudreDemandeReset($demande);

        return response()->json([
            'message' => 'Demande traitée. Le nouveau mot de passe a été envoyé par email.',
        ]);
    }
}
