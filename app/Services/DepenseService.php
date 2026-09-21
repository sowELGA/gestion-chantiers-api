<?php

namespace App\Services;

use App\Models\Chantier;
use App\Models\DepensesChantier;

class DepenseService
{
    public function ajouter(Chantier $chantier, array $data): DepensesChantier
    {
        return DepensesChantier::create([
            'categorie'    => $data['categorie'],
            'montant'      => $data['montant'],
            'description'  => $data['description'],
            'date_depense' => $data['date_depense'],
            'chantier_id'  => $chantier->id,
        ]);
    }

    public function supprimer(DepensesChantier $depense): void
    {
        $depense->delete();
    }
}
