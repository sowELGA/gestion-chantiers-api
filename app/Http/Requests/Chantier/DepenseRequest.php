<?php

namespace App\Http\Requests\Chantier;

use Illuminate\Foundation\Http\FormRequest;

class DepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // filtré par role:daf + daf.permission:depenses sur la route
    }

    public function rules(): array
    {
        return [
            'categorie'    => 'required|in:materiaux,materiels,salaires,autre',
            'montant'      => 'required|numeric|min:1',
            'description'  => 'required|string|max:255',
            'date_depense' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'categorie.required'    => 'La catégorie est obligatoire.',
            'categorie.in'          => 'La catégorie sélectionnée est invalide.',
            'montant.required'      => 'Le montant est obligatoire.',
            'montant.min'           => 'Le montant doit être supérieur à 0.',
            'description.required'  => 'La description est obligatoire.',
            'date_depense.required' => 'La date est obligatoire.',
        ];
    }
}
