<?php

namespace App\Http\Requests\Ouvrier;

use Illuminate\Foundation\Http\FormRequest;

class OuvrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // filtré par role:responsable_rh sur la route
    }

    public function rules(): array
    {
        return [
            'nomOuvrier'    => 'required|string|max:255',
            'prenomOuvrier' => 'required|string|max:255',
            'telOuvrier'    => 'required|string|max:20',
            'poste_id'      => 'required|exists:postes,id',
            'chantier_id'   => 'nullable|exists:chantiers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nomOuvrier.required'    => 'Le nom est obligatoire.',
            'prenomOuvrier.required' => 'Le prénom est obligatoire.',
            'telOuvrier.required'    => 'Le téléphone est obligatoire.',
            'poste_id.required'      => 'Le poste est obligatoire.',
            'poste_id.exists'        => 'Le poste sélectionné est invalide.',
            'chantier_id.exists'     => 'Le chantier sélectionné est invalide.',
        ];
    }
}
