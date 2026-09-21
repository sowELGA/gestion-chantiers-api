<?php

namespace App\Http\Requests\Chantier;

use App\Models\Chantier;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chef_projet_id' => 'nullable|integer',
            'pointeur_id'    => 'nullable|integer',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('chef_projet_id')) {
                $valide = User::avecRole('chef_projet')->where('actif', true)->find($this->chef_projet_id);
                if (!$valide) {
                    $validator->errors()->add('chef_projet_id', 'Le chef de projet sélectionné est invalide ou inactif.');
                }
            }

            if ($this->filled('pointeur_id')) {
                $valide = User::avecRole('pointeur')->where('actif', true)->find($this->pointeur_id);
                if (!$valide) {
                    $validator->errors()->add('pointeur_id', 'Le pointeur sélectionné est invalide ou inactif.');
                }

                $chantier = $this->route('chantier');
                $dejaAssigneAilleurs = Chantier::where('pointeur_id', $this->pointeur_id)
                    ->when($chantier, fn($q) => $q->where('id', '!=', $chantier->id))
                    ->exists();

                if ($dejaAssigneAilleurs) {
                    $validator->errors()->add('pointeur_id', 'Ce pointeur est déjà affecté à un autre chantier.');
                }
            }
        });
    }
}
