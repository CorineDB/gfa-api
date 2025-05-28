<?php

namespace App\Http\Requests\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception;

use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
use App\Rules\DistinctAttributeRule;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-un-formulaire-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->formulaire_de_perception))
        {
            $this->formulaire_de_perception = FormulaireDePerceptionDeGouvernance::findByKey($this->formulaire_de_perception);
        }

        return [
            'libelle'  => ['sometimes','max:255', Rule::unique('formulaires_de_perception_de_gouvernance', 'libelle')->where("programmeId", auth()->user()->programmeId)->ignore($this->formulaire_de_perception)->whereNull('deleted_at')],

            'description' => 'nullable|max:255',

            'perception' => ["sometimes","array", "min:2"],
            'perception.options_de_reponse' => ['sometimes', "array", "min:2"],
            'perception.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponseGouvernance())],
            'perception.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],

            'perception.principes_de_gouvernance' => ["sometimes", "array", "min:1"],
            'perception.principes_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new PrincipeDeGouvernancePerception())],
            //'perception.principes_de_gouvernance.*.position' => ["required", new DistinctAttributeRule(), "min:1"],
            'perception.principes_de_gouvernance.*.questions_operationnelle' => ["sometimes", "array", "min:1"],
            'perception.principes_de_gouvernance.*.questions_operationnelle.*' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new QuestionOperationnelle())],
            //'perception.principes_de_gouvernance.*.questions_operationnelle.*.position' => ["required", new DistinctAttributeRule(), "min:1"],
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
            // Custom messages for the 'nom' field
            'nom.required'      => 'Le champ nom est obligatoire.',
            'nom.max'           => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique'        => 'Ce nom est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'programmeId' field
            'programmeId.required' => 'Le champ programme est obligatoire.',

        ];
    }
}
