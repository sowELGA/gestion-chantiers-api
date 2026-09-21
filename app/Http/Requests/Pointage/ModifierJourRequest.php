<?php

namespace App\Http\Requests\Pointage;

use Illuminate\Foundation\Http\FormRequest;

class ModifierJourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'pointages' => 'required|array|min:1',
            'pointages.*.ouvrier_id' => 'required|exists:ouvriers,id',
            'pointages.*.statutPointage' => 'required|in:present,absent',
            'pointages.*.heures_sup' => 'nullable|numeric|min:0|max:12',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'La date est obligatoire.',
            'pointages.*.statutPointage.required' => 'Le statut est obligatoire.',
            'pointages.*.statutPointage.in' => 'Statut invalide.',
        ];
    }
}
