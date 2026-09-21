<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovisionnementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'designation'              => $this->designation,
            'quantite_demandee'        => (float) $this->quantite_demandee,
            'unite'                    => $this->unite,
            'priorite'                 => $this->priorite,
            'statutAppro'              => $this->statutAppro,
            'quantite_restante'        => $this->quantite_restante,
            'date_livraison_souhaitee' => $this->date_livraison_souhaitee?->format('Y-m-d'),
            'date_commande'            => $this->date_commande?->format('Y-m-d'),
            'date_livraison_prevue'    => $this->date_livraison_prevue?->format('Y-m-d'),
            'chantier'                 => $this->whenLoaded('chantier', fn() => [
                'id' => $this->chantier->id,
                'nomChantier' => $this->chantier->nomChantier,
            ]),
            'demandeur' => $this->whenLoaded('demandeur', fn() => [
                'id' => $this->demandeur->id,
                'nomComplet' => $this->demandeur->nom_complet,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bonReceptions' => $this->whenLoaded('bonReceptions', fn() => $this->bonReceptions->map(fn($b) => [
                'id' => $b->id,
                'quantite_recue' => (float) $b->quantite_recue,
                'date_reception' => $b->date_reception?->format('Y-m-d'),
            ])),
        ];
    }
}
