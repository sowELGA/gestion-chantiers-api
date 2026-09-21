<?php

namespace App\Http\Requests\Rapport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RapportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // filtré par le middleware role:chef_projet sur la route
    }

    public function rules(): array
    {
        return [
            'titre'        => 'required|string|max:255',
            'date_rapport' => 'required|date',
            'type'         => 'required|in:avancement,incident,livraison,reunion,autre',
            'contenu'      => 'required|string|min:10',
            'chantier_id'  => ['required', Rule::exists('chantiers', 'id')->where('chef_projet_id', auth()->id())],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required'        => 'Le titre est obligatoire.',
            'date_rapport.required' => 'La date est obligatoire.',
            'type.required'         => 'Le type de rapport est obligatoire.',
            'contenu.required'      => 'Le contenu est obligatoire.',
            'contenu.min'           => 'Le contenu doit contenir au moins 10 caractères.',
            'chantier_id.required'  => 'Le chantier est obligatoire.',
            'chantier_id.exists'    => 'Ce chantier ne vous est pas affecté.',
        ];
    }
}
