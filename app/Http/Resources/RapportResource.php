<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RapportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'date_rapport' => $this->date_rapport?->format('Y-m-d'),
            'contenu' => $this->contenu,
            'chantier' => $this->whenLoaded('chantier', fn() => ['id' => $this->chantier->id, 'nomChantier' => $this->chantier->nomChantier]),
            'auteur' => $this->whenLoaded('auteur', fn() => ['id' => $this->auteur->id, 'nomComplet' => $this->auteur->nom_complet]),
            'created_at' => $this->created_at,
        ];
    }
}
