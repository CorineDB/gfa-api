<?php

namespace App\Http\Requests\indicateur;

use App\Models\IndicateurValueKey;
use App\Models\Unitee;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeIndicateurTypeRequest extends FormRequest
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
        return [
            'agreger' => ['required', 'boolean'],

            // Pour passage vers agrégé : clés obligatoires
            'value_keys' => [
                Rule::requiredIf(request()->input('agreger')),
                request()->input('agreger') ? "array" : "",
                request()->input('agreger') ? "min:2" : ""
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

            // Pour passage vers simple : unité de mesure obligatoire
            'uniteeMesureId' => [
                Rule::requiredIf(!request()->input('agreger')),
                new HashValidatorRule(new Unitee())
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
            'agreger.required' => 'Le type d\'indicateur (agrégé ou simple) doit être spécifié.',
            'value_keys.required' => 'Les clés de valeurs sont obligatoires pour un indicateur agrégé.',
            'value_keys.min' => 'Un indicateur agrégé doit avoir au moins 2 clés de valeurs.',
            'uniteeMesureId.required' => 'L\'unité de mesure est obligatoire pour un indicateur simple.',
        ];
    }
}