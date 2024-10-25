<?php

namespace App\Http\Requests\formulaires_de_gouvernance;

use App\Models\CritereDeGouvernance;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\PrincipeDeGouvernance;
use App\Models\Programme;
use App\Models\TypeDeGouvernance;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'options_de_reponse' => ["array", "min:1"],
            'options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponse())],
            'options_de_reponse.*.point' => ["required", "distinct", "decimal:0,2", "min:0", "max:1"],
            'factuel.indicateurs_de_gouvernance' => ["array", "min:1"],
            'factuel.indicateurs_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new IndicateurDeGouvernance())],
            'factuel.indicateurs_de_gouvernance.*.critereDeGouvernanceId' => ["required", "distinct", new HashValidatorRule(new CritereDeGouvernance())],
            'factuel.indicateurs_de_gouvernance.*.principeDeGouvernanceId' => ["required", "distinct", new HashValidatorRule(new PrincipeDeGouvernance())],
            'factuel.indicateurs_de_gouvernance.*.typeDeGouvernanceId' => ["required", "distinct", new HashValidatorRule(new TypeDeGouvernance())],
            'perception.indicateurs_de_gouvernance' => ["array", "min:1"],
            'perception.indicateurs_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new IndicateurDeGouvernance())],
            'perception.indicateurs_de_gouvernance.*.principeDeGouvernanceId' => ["required", "distinct", new HashValidatorRule(new PrincipeDeGouvernance())]
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
