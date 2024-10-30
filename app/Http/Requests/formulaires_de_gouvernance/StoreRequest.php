<?php

namespace App\Http\Requests\formulaires_de_gouvernance;

use App\Models\CritereDeGouvernance;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\PrincipeDeGouvernance;
use Illuminate\Validation\Rule;
use App\Models\TypeDeGouvernance;
use App\Rules\DistinctAttributeRule;
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
            'libelle'          => 'required|max:255|unique:formulaires_de_gouvernance,libelle',
            'annee_exercice'   => ['required', 'integer', Rule::unique('formulaires_de_gouvernance', 'annee_exercice')
                ->where(function ($query) {
                    return $query->where('type', request()->input('type'));
                })
            ],
            'description'       => 'nullable|max:255',
            'type'             => 'required|string|in:factuel,perception',
            'lien'             => 'nullable|string',

            /*'options_de_reponse' => ["array", "min:1"],
            'options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponse())],
            'options_de_reponse.*.point' => ["required", "distinct", "decimal:0,2", "min:0", "max:1"],*/
            'factuel' => [Rule::requiredIf(request()->input('type') == 'factuel'), "array", "min:2"],
            'factuel.options_de_reponse' => [Rule::requiredIf(request()->input('type') == 'factuel'), "array", "min:2"],
            'factuel.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponse())],
            'factuel.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],

            'factuel.types_de_gouvernance' => [Rule::requiredIf(request()->input('type') == 'factuel'), "array", "min:1"],
            'factuel.types_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new TypeDeGouvernance())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new PrincipeDeGouvernance())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new CritereDeGouvernance())],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*' => ["required", /* "distinct",  */new HashValidatorRule(new IndicateurDeGouvernance())],

            'perception' => [Rule::requiredIf(request()->input('type') == 'perception'), "array", "min:2"],
            //'perception' => ["required", Rule::requiredIf(request()->input('type') == 'perception')],
            'perception.options_de_reponse' => [Rule::requiredIf(request()->input('type') == 'perception'), "array", "min:2"],
            'perception.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponse())],
            'perception.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],

            'perception.principes_de_gouvernance' => [Rule::requiredIf(request()->input('type') == 'perception'), "array", "min:1"],
            'perception.principes_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new PrincipeDeGouvernance())],
            'perception.principes_de_gouvernance.*.questions_operationnelle' => ["required", "array", "min:1"],
            'perception.principes_de_gouvernance.*.questions_operationnelle.*' => ["required", "distinct", new HashValidatorRule(new IndicateurDeGouvernance())]
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
