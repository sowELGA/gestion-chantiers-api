<?php

namespace App\Http\Requests\Ouvrier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:255', Rule::unique('postes', 'libelle')->ignore($this->route('poste'))],
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé du poste est obligatoire.',
            'libelle.unique'   => 'Ce poste existe déjà.',
        ];
    }
}
