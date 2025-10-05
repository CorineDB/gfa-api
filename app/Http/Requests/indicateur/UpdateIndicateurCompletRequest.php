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
            'description' => 'nullable|string',
            'type_de_variable' => 'sometimes|in:quantitatif,qualitatif,dichotomique',

            'sources_de_donnee' => 'nullable|string',
            'frequence_de_la_collecte' => 'nullable|string',
            'methode_de_la_collecte' => 'nullable|string',
            'hypothese' => 'nullable|string',

            'indice' => 'sometimes|integer|min:0',
            'uniteeMesureId' => ['sometimes', 'nullable', new HashValidatorRule(new Unitee())],
            'categorieId' => ['sometimes', new HashValidatorRule(new Categorie())],

            // Année de base
            'anneeDeBase' => [
                'sometimes',
                'nullable',
                'date_format:Y',
                'after_or_equal:' . Carbon::parse($programme->debut)->year,
                'before_or_equal:' . Carbon::parse($programme->fin)->year,
                'before_or_equal:' . now()->format("Y")
            ],

            // Type d'indicateur
            'agreger' => 'sometimes|boolean',

            // Clés de valeurs (pour agrégés)
            'value_keys' => [
                Rule::requiredIf(request()->input('agreger')),
                request()->input('agreger') ? "array" : "",
                function($_, $__, $fail) {
                    if (!request()->input('agreger') && is_array(request()->input('value_keys'))) {
                        $fail("Champ non requis pour un indicateur non agrégé.");
                    }
                },
                request()->input('agreger') ? "min:1" : ""
            ],
            'value_keys.*.id' => [
                Rule::requiredIf(request()->input('agreger')),
                "string",
                'distinct',
                new HashValidatorRule(new IndicateurValueKey())
            ],
            'value_keys.*.uniteeMesureId' => [
                "nullable",
                "string",
                new HashValidatorRule(new Unitee())
            ],

            // Valeur de base
            'valeurDeBase' => [
                'sometimes',
                request()->input('agreger') ? "array" : "",
                function($_, $value, $fail) {
                    $isAgreger = request()->input('agreger');
                    if ($isAgreger && !is_array($value)) {
                        $fail("Pour un indicateur agrégé, la valeur de base doit être un tableau.");
                    }
                    if (!$isAgreger && is_array($value)) {
                        $fail("Pour un indicateur simple, la valeur de base doit être une valeur unique.");
                    }
                },
                request()->input('agreger') ? "min:1" : ""
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
            'anneesCible' => ['sometimes', 'nullable', "array", request()->input('anneesCible') ? "min:1" : ""],
            'anneesCible.*.annee' => [
                'required_with:anneesCible',
                'distinct',
                'date_format:Y',
                'after_or_equal:anneeDeBase',
                'after_or_equal:' . Carbon::parse($programme->debut)->year,
                'before_or_equal:' . Carbon::parse($programme->fin)->year
            ],
            'anneesCible.*.valeurCible' => [
                'required_with:anneesCible',
                request()->input('agreger') ? "array" : "",
                function($_, $value, $fail) {
                    $isAgreger = request()->input('agreger');
                    if ($isAgreger && !is_array($value)) {
                        $fail("Pour un indicateur agrégé, la valeur cible doit être un tableau.");
                    }
                    if (!$isAgreger && is_array($value)) {
                        $fail("Pour un indicateur simple, la valeur cible doit être une valeur unique.");
                    }
                },
                request()->input('agreger') ? "min:1" : ""
            ],
            'anneesCible.*.valeurCible.*.keyId' => [
                Rule::requiredIf(request()->input('agreger') && request()->has('anneesCible')),
                'distinct',
                new HashValidatorRule(new IndicateurValueKey())
            ],
            'anneesCible.*.valeurCible.*.value' => [
                Rule::requiredIf(request()->input('agreger') && request()->has('anneesCible'))
            ],

            // Responsables
            'responsables' => ['sometimes', 'array'],
            'responsables.ug' => [
                Rule::requiredIf(function() {
                    return request()->has('responsables') &&
                           empty(request()->input('responsables.organisations'));
                }),
                !empty(request()->input('responsables.organisations')) ? 'nullable' : '',
                'string',
                new HashValidatorRule(new UniteeDeGestion())
            ],
            'responsables.organisations' => [
                Rule::requiredIf(function() {
                    return request()->has('responsables') &&
                           empty(request()->input('responsables.ug'));
                }),
                'array',
                'min:0'
            ],
            'responsables.organisations.*' => [
                'distinct',
                'string',
                new HashValidatorRule(new Organisation())
            ],

            // Sites
            'sites' => ['sometimes', 'nullable', "array", request()->input('sites') ? "min:1" : ""],
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
            'nom.unique' => 'Ce nom d\'indicateur existe déjà dans ce programme.',
            'nom.string' => 'Le nom de l\'indicateur doit être une chaîne de caractères.',

            'type_de_variable.in' => 'Le type de variable doit être quantitatif, qualitatif ou dichotomique.',

            'indice.integer' => 'L\'indice doit être un nombre entier.',
            'indice.min' => 'L\'indice doit être supérieur ou égal à 0.',

            'anneeDeBase.date_format' => 'L\'année de base doit être au format YYYY.',
            'anneeDeBase.after_or_equal' => 'L\'année de base doit être postérieure ou égale au début du programme.',
            'anneeDeBase.before_or_equal' => 'L\'année de base doit être antérieure ou égale à l\'année en cours.',

            'agreger.boolean' => 'Le champ agreger doit être un booléen.',

            'value_keys.required' => 'Les clés de valeurs sont obligatoires pour un indicateur agrégé.',
            'value_keys.array' => 'Les clés de valeurs doivent être un tableau.',
            'value_keys.min' => 'Au moins une clé de valeur doit être fournie pour un indicateur agrégé.',
            'value_keys.*.id.required' => 'L\'identifiant de la clé est obligatoire.',
            'value_keys.*.id.distinct' => 'Les identifiants des clés doivent être uniques.',

            'valeurDeBase.array' => 'La valeur de base doit être un tableau pour un indicateur agrégé.',
            'valeurDeBase.min' => 'Au moins une valeur de base doit être fournie.',
            'valeurDeBase.*.keyId.required' => 'Le keyId est obligatoire pour chaque valeur de base.',
            'valeurDeBase.*.keyId.distinct' => 'Les keyId doivent être uniques dans les valeurs de base.',
            'valeurDeBase.*.value.required' => 'La valeur est obligatoire pour chaque valeur de base.',

            'anneesCible.array' => 'Les années cibles doivent être un tableau.',
            'anneesCible.min' => 'Au moins une année cible doit être fournie.',
            'anneesCible.*.annee.required_with' => 'L\'année est obligatoire.',
            'anneesCible.*.annee.distinct' => 'Les années doivent être uniques.',
            'anneesCible.*.annee.date_format' => 'L\'année doit être au format YYYY.',
            'anneesCible.*.annee.after_or_equal' => 'L\'année cible doit être postérieure ou égale à l\'année de base et au début du programme.',
            'anneesCible.*.annee.before_or_equal' => 'L\'année cible doit être antérieure ou égale à la fin du programme.',
            'anneesCible.*.valeurCible.required_with' => 'La valeur cible est obligatoire.',
            'anneesCible.*.valeurCible.array' => 'La valeur cible doit être un tableau pour un indicateur agrégé.',
            'anneesCible.*.valeurCible.min' => 'Au moins une valeur cible doit être fournie.',
            'anneesCible.*.valeurCible.*.keyId.required' => 'Le keyId est obligatoire pour chaque valeur cible.',
            'anneesCible.*.valeurCible.*.keyId.distinct' => 'Les keyId doivent être uniques dans les valeurs cibles.',
            'anneesCible.*.valeurCible.*.value.required' => 'La valeur est obligatoire pour chaque valeur cible.',

            'responsables.array' => 'Les responsables doivent être un tableau.',
            'responsables.ug.required' => 'L\'unité de gestion responsable est obligatoire si aucune organisation n\'est spécifiée.',
            'responsables.organisations.required' => 'Au moins une organisation responsable est obligatoire si aucune unité de gestion n\'est spécifiée.',
            'responsables.organisations.array' => 'Les organisations responsables doivent être un tableau.',
            'responsables.organisations.*.distinct' => 'Les organisations responsables doivent être uniques.',

            'sites.array' => 'Les sites doivent être un tableau.',
            'sites.min' => 'Au moins un site doit être fourni.',
            'sites.*.distinct' => 'Les sites doivent être uniques.',
        ];
    }
}
