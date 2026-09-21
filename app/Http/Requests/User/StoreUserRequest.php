<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // déjà filtré par le middleware role:admin sur la route
    }

    public function rules(): array
    {
        return [
            'nomUser'    => 'required|string|max:255',
            'prenomUser' => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'telUser'    => 'required|string|max:20',

            'roles'                              => 'required|array|min:1',
            'roles.*.id'                          => 'required|exists:roles,id',
            'roles.*.gere_approvisionnements'     => 'sometimes|boolean',
            'roles.*.gere_depenses'               => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'    => 'Cet email est déjà utilisé.',
            'roles.required'  => 'Vous devez sélectionner au moins un rôle.',
            'roles.*.id.exists' => 'Un des rôles sélectionnés est invalide.',
        ];
    }
}
