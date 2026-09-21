<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'nomUser'            => $this->nomUser,
            'prenomUser'         => $this->prenomUser,
            'nomComplet'         => $this->nom_complet,
            'email'              => $this->email,
            'telUser'            => $this->telUser,
            'actif'              => $this->actif,
            'premiere_connexion' => $this->premiere_connexion,
            'roles'              => $this->roles->map(fn($role) => [
                'id'                      => $role->id,
                'nom'                     => $role->nom,
                'libelle'                 => $role->libelle,
                'exclusif'                => $role->exclusif,
                'gere_approvisionnements' => $role->pivot->gere_approvisionnements,
                'gere_depenses'           => $role->pivot->gere_depenses,
            ]),
        ];
    }
}
