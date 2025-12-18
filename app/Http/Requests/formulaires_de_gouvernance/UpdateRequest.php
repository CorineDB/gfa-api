<?php

namespace App\Http\Requests\formulaires_de_gouvernance;

use App\Models\CritereDeGouvernance;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\PrincipeDeGouvernance;
use App\Models\SourceDeVerification;
use App\Models\TypeDeGouvernance;
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
        return request()->user()->hasPermissionTo("modifier-un-evaluation-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->formulaire_de_gouvernance))
        {
            $this->formulaire_de_gouvernance = SourceDeVerification::findByKey($this->formulaire_de_gouvernance);
        }

        return [
            'libelle'               => ['sometimes', 'string', Rule::unique('formulaires_de_gouvernance', 'libelle')->ignore($this->formulaire_de_gouvernance)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'annee_exercice'   => ['sometimes', 'integer'/* , Rule::unique('formulaires_de_gouvernance', 'annee_exercice')
                ->where(function ($query) {
                    return $query->where('type', request()->input('type'));
                })->ignore($this->formulaire_de_gouvernance)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at') */
            ],
            'description'       => 'nullable|max:255',
            'type'             => 'sometimes|string|in:factuel,perception',
            'lien'             => 'nullable|string',

            'factuel' => ['sometimes', Rule::requiredIf(request()->input('type') == 'factuel'), "array", "min:2"],
            'factuel.options_de_reponse' => ['sometimes', Rule::requiredIf(request()->input('type') == 'factuel'), "array", "min:2"],
            'factuel.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponse())],
            'factuel.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],
            'factuel.options_de_reponse.*.preuveIsRequired' => ["sometimes", "boolean:false"],

            'factuel.types_de_gouvernance' => ['sometimes', Rule::requiredIf(request()->input('type') == 'factuel'), "array", "min:1"],
            'factuel.types_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new TypeDeGouvernance())],
            //'factuel.types_de_gouvernance.*.position' => ["required", new DistinctAttributeRule(), "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new PrincipeDeGouvernance())],
            //'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.position' => ["required", new DistinctAttributeRule(), "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new CritereDeGouvernance())],
            //'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.position' => ["required", new DistinctAttributeRule(), "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance' => ["required", "array", "min:1"],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*' => ["sometimes", /* "distinct",  */new HashValidatorRule(new IndicateurDeGouvernance()), new DistinctAttributeRule()],
            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*.id' => ["sometimes", new DistinctAttributeRule(), new HashValidatorRule(new IndicateurDeGouvernance())],

            'factuel.types_de_gouvernance.*.principes_de_gouvernance.*.criteres_de_gouvernance.*.indicateurs_de_gouvernance.*.position' => ["sometimes", new DistinctAttributeRule(), "min:1"],

            'perception' => ['sometimes', Rule::requiredIf(request()->input('type') == 'perception'), "array", "min:2"],
            //'perception' => ["required", Rule::requiredIf(request()->input('type') == 'perception')],
            'perception.options_de_reponse' => ['sometimes', Rule::requiredIf(request()->input('type') == 'perception'), "array", "min:2"],
            'perception.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponse())],
            'perception.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],

            'perception.principes_de_gouvernance' => ['sometimes', Rule::requiredIf(request()->input('type') == 'perception'), "array", "min:1"],
            'perception.principes_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new PrincipeDeGouvernance())],
            //'perception.principes_de_gouvernance.*.id' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new PrincipeDeGouvernance())],
            //'perception.principes_de_gouvernance.*.position' => ["required", new DistinctAttributeRule(), "min:1"],
            'perception.principes_de_gouvernance.*.questions_operationnelle' => ["required", "array", "min:1"],
            'perception.principes_de_gouvernance.*.questions_operationnelle.*' => ["required", new DistinctAttributeRule(), new HashValidatorRule(new IndicateurDeGouvernance())],
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
