<?php

namespace App\Http\Requests\Approvisionnement;

use App\Helpers\ApprovisionnementHelper;
use Illuminate\Foundation\Http\FormRequest;

class ReceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // rôle vérifié par le middleware, appartenance du chantier vérifiée dans le contrôleur
    }

    public function rules(): array
    {
        $demande = $this->route('demande');
        $quantiteRestante = $demande ? ApprovisionnementHelper::quantiteRestante($demande) : null;

        return [
            'quantite_recue' => array_filter([
                'required',
                'numeric',
                'gt:0',
                $quantiteRestante !== null ? "max:{$quantiteRestante}" : null,
            ]),
            'observation' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'quantite_recue.required' => 'La quantité reçue est obligatoire.',
            'quantite_recue.gt'       => 'La quantité doit être strictement supérieure à 0.',
            'quantite_recue.max'      => 'La quantité reçue dépasse la quantité restante à livrer.',
        ];
    }
}
