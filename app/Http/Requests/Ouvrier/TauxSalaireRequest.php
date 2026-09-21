<?php

namespace App\Http\Requests\Ouvrier;

use Illuminate\Foundation\Http\FormRequest;

class TauxSalaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'taux'                    => 'required|array',
            'taux.*.taux_journalier'  => 'nullable|numeric|min:0',
            'taux.*.taux_heure_sup'   => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'taux.*.taux_journalier.numeric' => 'Le taux journalier doit être un nombre.',
            'taux.*.taux_journalier.min'     => 'Le taux journalier ne peut pas être négatif.',
            'taux.*.taux_heure_sup.numeric'  => "Le taux heure sup doit être un nombre.",
            'taux.*.taux_heure_sup.min'      => "Le taux heure sup ne peut pas être négatif.",
        ];
    }
}
