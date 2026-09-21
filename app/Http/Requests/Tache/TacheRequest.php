<?php

namespace App\Http\Requests\Tache;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $phase = $this->route('phase');
        $tache = $this->route('tache'); // null en création

        return [
            'nomTache' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taches', 'nomTache')
                    ->where('phase_id', $phase?->id)
                    ->ignore($tache?->id),
            ],
            'date_debut_prevue' => array_filter([
                'required',
                'date',
                $phase?->date_debut ? 'after_or_equal:' . $phase->date_debut->format('Y-m-d') : null,
            ]),
            'date_fin_prevue' => array_filter([
                'required',
                'date',
                'after_or_equal:date_debut_prevue',
                $phase?->date_fin_prevue ? 'before_or_equal:' . $phase->date_fin_prevue->format('Y-m-d') : null,
            ]),
            'tache_precedente_id' => [
                'nullable',
                Rule::exists('taches', 'id')->where('phase_id', $phase?->id),
                $tache ? Rule::notIn([$tache->id]) : null,
            ],
        ];
    }

    public function messages(): array
    {
        $phase = $this->route('phase');

        return [
            'nomTache.required' => 'Le nom de la tâche est obligatoire.',
            'nomTache.unique'   => 'Une tâche avec ce nom existe déjà pour cette phase.',
            'date_debut_prevue.required' => 'La date de début est obligatoire.',
            'date_debut_prevue.after_or_equal' => 'La date de début doit être après ou égale au début de la phase'
                . ($phase?->date_debut ? ' (' . $phase->date_debut->format('d/m/Y') . ')' : '') . '.',
            'date_fin_prevue.required' => 'La date de fin est obligatoire.',
            'date_fin_prevue.after_or_equal' => 'La date de fin doit être après la date de début.',
            'date_fin_prevue.before_or_equal' => 'La date de fin doit être avant la fin de la phase'
                . ($phase?->date_fin_prevue ? ' (' . $phase->date_fin_prevue->format('d/m/Y') . ')' : '') . '.',
            'tache_precedente_id.exists' => 'La tâche précédente sélectionnée est invalide.',
            'tache_precedente_id.not_in' => 'Une tâche ne peut pas être sa propre tâche précédente.',
        ];
    }
}
