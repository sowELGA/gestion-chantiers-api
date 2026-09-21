<?php

namespace App\Http\Requests\Chantier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChantierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // déjà filtré par le middleware role:directeur_travaux sur la route
    }

    protected function prepareForValidation(): void
    {
        if ($this->budget_prevu === '') {
            $this->merge(['budget_prevu' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'nomChantier' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chantiers', 'nomChantier')->ignore($this->route('chantier')),
            ],
            'localisation'    => 'required|string|max:255',
            'budget_prevu'    => 'nullable|numeric|min:1',
            'date_debut'      => 'required|date',
            'date_fin_prevue' => 'required|date|after:date_debut',
            'chef_projet_id'  => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nomChantier.required'     => 'Le nom du chantier est obligatoire.',
            'nomChantier.unique'       => 'Le nom du chantier existe déjà.',
            'localisation.required'    => 'La localisation est obligatoire.',
            'budget_prevu.numeric'     => 'Le budget doit être un nombre.',
            'budget_prevu.min'         => 'Le budget doit être supérieur à 0.',
            'date_debut.required'      => 'La date de début est obligatoire.',
            'date_fin_prevue.required' => 'La date de fin est obligatoire.',
            'date_fin_prevue.after'    => 'La date de fin doit être après la date de début.',
        ];
    }
}
