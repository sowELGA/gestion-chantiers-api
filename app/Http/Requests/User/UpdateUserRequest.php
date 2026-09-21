<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'nomUser'    => 'required|string|max:255',
            'prenomUser' => 'required|string|max:255',
            'email'      => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'telUser'    => 'required|string|max:20',

            'roles'                          => 'required|array|min:1',
            'roles.*.id'                      => 'required|exists:roles,id',
            'roles.*.gere_approvisionnements' => 'sometimes|boolean',
            'roles.*.gere_depenses'           => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'   => 'Cet email est déjà utilisé.',
            'roles.required' => 'Vous devez sélectionner au moins un rôle.',
        ];
    }
}
