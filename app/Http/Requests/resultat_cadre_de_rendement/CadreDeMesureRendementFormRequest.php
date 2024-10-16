<?php

namespace App\Http\Requests\resultat_cadre_de_rendement;

use App\Models\Indicateur;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\ResultatCadreDeRendement;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CadreDeMesureRendementFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'projetId'                                                          => ['sometimes', new HashValidatorRule(new Projet())],
            'resultats_cadre_de_mesure_rendement'                                         => ['required', 'array', 'min:1'],
            'resultats_cadre_de_mesure_rendement.*.resultatCadreDeRendementId'            => ['distinct', new HashValidatorRule(new ResultatCadreDeRendement())],
            'resultats_cadre_de_mesure_rendement.*.type'                                  => ['required', 'string', 'in:impact,effet,produit'],
            'resultats_cadre_de_mesure_rendement.*.position'                              => ['required', 'integer', 'min:0'],
            'resultats_cadre_de_mesure_rendement.*.parentResultatCadreDeRendementId'      => ['sometimes', 'nullable', new HashValidatorRule(new ResultatCadreDeRendement())],
            'resultats_cadre_de_mesure_rendement.*.indicateurs'                           => ['required', 'array', 'min:1'],
            'resultats_cadre_de_mesure_rendement.*.indicateurs.*.indicateurId'            => ['required', new HashValidatorRule(new Indicateur())],
            'resultats_cadre_de_mesure_rendement.*.indicateurs.*.position'                => ['required', 'integer', 'min:1']
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
            // Custom messages for the 'libelle' field
            'libelle.required'      => 'Le champ libelle est obligatoire.',
            'libelle.max'           => 'Le libelle ne doit pas dépasser 255 caractères.',
            'libelle.unique'        => 'Ce libelle est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'programmeId' field
            'programmeId.required' => 'Le champ programme est obligatoire.',
        ];
    }
}
