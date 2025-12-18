<?php

namespace App\Http\Requests\indicateur;

use App\Models\IndicateurValueKey;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateValeurDeBaseRequest extends FormRequest
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
        // On récupère l'indicateur pour déterminer s'il est agrégé
        $indicateur = request()->route('indicateur');
        $isAgreger = false;

        if ($indicateur) {
            // Essayer de récupérer l'indicateur depuis la route
            $indicateurModel = \App\Models\Indicateur::find($indicateur);
            $isAgreger = $indicateurModel ? $indicateurModel->agreger : false;
        }

        return [
            'valeurDeBase' => [
                'required',
                function($attribute, $value, $fail) use ($isAgreger) {
                    if ($isAgreger && !is_array($value)) {
                        $fail("Pour un indicateur agrégé, la valeur de base doit être un tableau avec les clés correspondantes.");
                    }
                    if (!$isAgreger && is_array($value)) {
                        $fail("Pour un indicateur simple, la valeur de base doit être une valeur unique.");
                    }
                }
            ],

            // Pour indicateurs agrégés
            'valeurDeBase.*.keyId' => [
                Rule::requiredIf($isAgreger),
                'distinct',
                new HashValidatorRule(new IndicateurValueKey())
            ],
            'valeurDeBase.*.value' => [
                Rule::requiredIf($isAgreger)
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
            'valeurDeBase.required' => 'La valeur de base est obligatoire.',
            'valeurDeBase.*.keyId.required' => 'L\'ID de la clé est obligatoire pour chaque valeur.',
            'valeurDeBase.*.value.required' => 'La valeur est obligatoire pour chaque clé.',
        ];
    }
}