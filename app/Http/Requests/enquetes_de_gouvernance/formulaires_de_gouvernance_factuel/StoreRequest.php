<?php

namespace App\Http\Requests\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel;

use App\Models\enquetes_de_gouvernance\CritereDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DistinctAttributeRule;
use App\Rules\HashValidatorRule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-un-formulaire-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'libelle'           => ['required', 'string', Rule::unique('formulaires_factuel_de_gouvernance', 'libelle')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'description' => 'nullable|max:255',

            'factuel' => ["required","array", "min:2"],
            'factuel.options_de_reponse' => ["required","array", "min:2"],
            'factuel.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponseGouvernance())],
            'factuel.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],
            'factuel.options_de_reponse.*.preuveIsRequired' => ["sometimes", "boolean:false"],
            'factuel.options_de_reponse.*.sourceIsRequired' => ["sometimes", "boolean:false"],
            'factuel.options_de_reponse.*.descriptionIsRequired' => ["sometimes", "boolean:false"],

            'factuel.types_de_gouvernance' => ["required","array", "min:1"],
            'factuel.types_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new TypeDeGouvernanceFactuel())],
            'factuel.types_de_gouvernance.*.position' => ["sometimes", new DistinctAttributeRule(), "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new PrincipeDeGouvernanceFactuel())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.position' => ["sometimes", new DistinctAttributeRule(), "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new CritereDeGouvernanceFactuel())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.position' => ["sometimes", new DistinctAttributeRule(), "min:1"],
            //'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance' => ["required", "array", "min:1"],
            //'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new IndicateurDeGouvernanceFactuel())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new IndicateurDeGouvernanceFactuel())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*.position' => ["required", 'numeric', new DistinctAttributeRule(), "min:1"]
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
            // General
            'nom.required' => 'Le champ "Nom" est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.unique' => 'Ce nom est déjà utilisé pour ce programme.'

        ];
    }
}
