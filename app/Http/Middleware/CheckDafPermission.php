<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDafPermission
{
    /**
     * @param string $capacite 'approvisionnements' ou 'depenses'
     */
    public function handle(Request $request, Closure $next, string $capacite): mixed
    {
        $user = $request->user();

        if (!$user || !$user->actif) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $autorise = match ($capacite) {
            'approvisionnements' => $user->peutGererApprovisionnements(),
            'depenses'           => $user->peutGererDepenses(),
            default              => false,
        };

        if (!$autorise) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de gérer les ' . $capacite . '.'], 403);
        }

        return $next($request);
    }
}
