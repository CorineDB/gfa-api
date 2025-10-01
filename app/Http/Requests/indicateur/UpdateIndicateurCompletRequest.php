<?php

namespace App\Http\Requests\indicateur;

use App\Models\Categorie;
use App\Models\IndicateurValueKey;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\Unitee;
use App\Models\UniteeDeGestion;
use App\Rules\HashValidatorRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIndicateurCompletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-un-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $programme = auth()->user()->programme;
        $indicateur = request()->route('indicateur');

        return [
            // Informations de base
            'nom' => [
                'sometimes',
                'string',
                Rule::unique('indicateurs', 'nom')
                    ->where("programmeId", auth()->user()->programmeId)
                    ->whereNull('deleted_at')
                    ->ignore($indicateur)
            ],
            'description' => 'sometimes|string',
            'type_de_variable' => 'sometimes|in:quantitatif,qualitatif,dichotomique',
            'hypothese' => 'sometimes|string',
            'indice' => 'sometimes|integer|min:0',
            'uniteeMesureId' => ['sometimes', new HashValidatorRule(new Unitee())],
            'categorieId' => ['sometimes', new HashValidatorRule(new Categorie())],
            'methode_de_la_collecte' => 'sometimes|string',
            'frequence_de_la_collecte' => 'sometimes|string',
            'sources_de_donnee' => 'sometimes|string',

            // Type d'indicateur
            'agreger' => 'sometimes|boolean',

            // Clés de valeurs (pour agrégés)
            'value_keys' => [
                Rule::requiredIf(request()->input('agreger')),
                request()->input('agreger') ? "array" : "",
                request()->input('agreger') ? "min:1" : ""
            ],
            'value_keys.*.id' => [
                Rule::requiredIf(request()->input('agreger')),
                "string",
                'distinct',
                new HashValidatorRule(new IndicateurValueKey())
            ],

            // Valeur de base
            'valeurDeBase' => [
                'sometimes',
                function($attribute, $value, $fail) {
                    $isAgreger = request()->input('agreger');
                    if ($isAgreger && !is_array($value)) {
                        $fail("Pour un indicateur agrégé, la valeur de base doit être un tableau.");
                    }
                    if (!$isAgreger && is_array($value)) {
                        $fail("Pour un indicateur simple, la valeur de base doit être une valeur unique.");
                    }
                }
            ],
            'valeurDeBase.*.keyId' => [
                Rule::requiredIf(request()->input('agreger') && request()->has('valeurDeBase')),
                'distinct',
                new HashValidatorRule(new IndicateurValueKey())
            ],
            'valeurDeBase.*.value' => [
                Rule::requiredIf(request()->input('agreger') && request()->has('valeurDeBase'))
            ],

            // Valeurs cibles
            'anneesCible' => ['sometimes', "array"],
            'anneesCible.*.annee' => [
                'required_with:anneesCible',
                'distinct',
                'integer',
                'min:' . Carbon::parse($programme->debut)->year,
                'max:' . Carbon::parse($programme->fin)->year
            ],
            'anneesCible.*.valeurCible' => [
                'required_with:anneesCible',
                function($attribute, $value, $fail) {
                    $isAgreger = request()->input('agreger');
                    if ($isAgreger && !is_array($value)) {
                        $fail("Pour un indicateur agrégé, la valeur cible doit être un tableau.");
                    }
                    if (!$isAgreger && is_array($value)) {
                        $fail("Pour un indicateur simple, la valeur cible doit être une valeur unique.");
                    }
                }
            ],

            // Responsables
            'responsables' => ['sometimes', 'array'],
            'responsables.ug' => [
                'sometimes',
                'string',
                new HashValidatorRule(new UniteeDeGestion())
            ],
            'responsables.organisations' => ['sometimes', 'array'],
            'responsables.organisations.*' => [
                'distinct',
                'string',
                new HashValidatorRule(new Organisation())
            ],

            // Sites
            'sites' => ['sometimes', "array"],
            'sites.*' => ['distinct', new HashValidatorRule(new Site())],
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
            'nom.unique' => 'Ce nom d\'indicateur existe déjà.',
            'value_keys.required' => 'Les clés de valeurs sont obligatoires pour un indicateur agrégé.',
            'anneesCible.*.annee.distinct' => 'Les années doivent être uniques.',
            'responsables.organisations.*.distinct' => 'Les organisations responsables doivent être uniques.',
            'sites.*.distinct' => 'Les sites doivent être uniques.',
        ];
    }
}