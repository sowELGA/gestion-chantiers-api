<?php

namespace App\Http\Requests\Approvisionnement;

use Illuminate\Foundation\Http\FormRequest;

class ApprovisionnementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // filtré par le middleware role:chef_projet sur la route
    }

    public function rules(): array
    {
        return [
            'chantier_id' => 'required|exists:chantiers,id',
            'demandes' => 'required|array|min:1',
            'demandes.*.designation' => 'required|string|max:255',
            'demandes.*.quantite_demandee' => 'required|numeric|min:0.1',
            'demandes.*.unite' => 'required|string|max:50',
            'demandes.*.date_livraison_souhaitee' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'chantier_id.required' => 'Le chantier est obligatoire.',
            'demandes.required' => 'Vous devez ajouter au moins une demande.',
            'demandes.*.designation.required' => 'La désignation est obligatoire.',
            'demandes.*.quantite_demandee.required' => 'La quantité est obligatoire.',
            'demandes.*.quantite_demandee.min' => 'La quantité doit être supérieure à 0.',
            'demandes.*.unite.required' => "L'unité est obligatoire.",
            'demandes.*.date_livraison_souhaitee.required' => 'La date de livraison est obligatoire.',
            'demandes.*.date_livraison_souhaitee.after_or_equal' => "La date doit être aujourd'hui ou dans le futur.",
        ];
    }
}
