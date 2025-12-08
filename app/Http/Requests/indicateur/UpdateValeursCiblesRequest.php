<?php

namespace App\Http\Requests\indicateur;

use App\Models\IndicateurValueKey;
use App\Rules\HashValidatorRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateValeursCiblesRequest extends FormRequest
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

        return [
            'agreger' => ['sometimes', 'boolean'],

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

            'anneesCible' => ['required', "array", "min:1"],
            'anneesCible.*.annee' => [
                'required',
                'distinct',
                'integer',
                'min:' . Carbon::parse($programme->debut)->year,
                'max:' . Carbon::parse($programme->fin)->year
            ],
            'anneesCible.*.valeurCible' => [
                'required',
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

            // Pour indicateurs agrégés
            'anneesCible.*.valeurCible.*.keyId' => [
                Rule::requiredIf(request()->input('agreger')),
                'distinct',
                new HashValidatorRule(new IndicateurValueKey())
            ],
            'anneesCible.*.valeurCible.*.value' => [
                Rule::requiredIf(request()->input('agreger'))
            ],
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
            'anneesCible.required' => 'Les années cibles sont obligatoires.',
            'anneesCible.*.annee.required' => 'L\'année doit être spécifiée pour chaque valeur cible.',
            'anneesCible.*.annee.distinct' => 'Les années doivent être uniques.',
            'anneesCible.*.valeurCible.required' => 'La valeur cible est obligatoire.',
            'value_keys.required' => 'Les clés de valeurs sont obligatoires pour un indicateur agrégé.',
        ];
    }
}