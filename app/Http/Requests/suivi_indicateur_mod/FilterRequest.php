<?php

namespace App\Http\Requests\suivi_indicateur_mod;

use App\Models\MOD;
use App\Models\Categorie;
use App\Models\Indicateur;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dateSuivie'        => 'required|date',
            'indicateurId'      => ['sometimes', new HashValidatorRule(new Indicateur()) ],
            'categorieId'      => ['sometimes',  new HashValidatorRule(new Categorie()) ],
            'modId'      => ['sometimes',  new HashValidatorRule(new MOD()) ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date_debut.required'               => 'Veuillez préciser la date de debut.',
            'date_debut.date_format'            => 'La date de debut doit respecter le format de date Y-m-d.',
            'date_debut.gte'                    => 'La date de debut doit être après 31 Décembre 1939.',

            'date_fin.required'                 => 'Veuillez préciser la date de fin.',
            'date_fin.date_format'              => 'La date de debut doit respecter le format de date Y-m-d.',
            'date_fin.after_or_equal'           => 'La date de fin doit être une date après ou equal à la date de début.',

            'indicateurId.required'             => 'Veuillez préciser le indicateur auquelle sera associé le suivi .',
            'indicateurId.exists'               => 'Indicateur inexistant. Veuillez préciser un indicateur existant.'
        ];
    }

    protected function prepareForValidation(): void
    {
        if($this->indicateurId)
        {
            $indicateur = Indicateur::decodeKey($this->indicateurId);

            if(!$indicateur)
                throw ValidationException::withMessages(['indicateurId' => "Indicateur inconnue"]);

            $this->merge([
                'indicateurId' => $indicateur
            ]);
        }

        if($this->categorieId)
        {
            $categorie = Categorie::decodeKey($this->categorieId);

            if(!$categorie)
                throw ValidationException::withMessages(['categorieId' => "Categorie inconnue"]);

            $this->merge([
                'categorieId' => $categorie
            ]);
        }

        if($this->modId)
        {
            $mod = MOD::decodeKey($this->modId);

            if(!$mod)
                throw ValidationException::withMessages(['modId' => "Mod inconnue"]);

            $this->merge([
                'modId' => $mod
            ]);
        }
    }
}
