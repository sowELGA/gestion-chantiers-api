<?php

namespace App\Http\Requests\Pointage;

use Illuminate\Foundation\Http\FormRequest;

class RejetRecapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // filtré par role:chef_projet sur la route
    }

    public function rules(): array
    {
        return ['motif_rejet' => 'required|string|min:10|max:500'];
    }

    public function messages(): array
    {
        return [
            'motif_rejet.required' => 'Le motif du rejet est obligatoire.',
            'motif_rejet.min' => 'Le motif doit contenir au moins 10 caractères.',
        ];
    }
}
