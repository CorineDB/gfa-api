<?php

namespace App\Http\Requests\pta;

use App\Models\Bailleur;
use App\Models\Organisation;
use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class FiltreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("voir-ptab") || request()->user()->hasRole("unitee-de-gestion", "organisation");

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            /*'programmeId' => ['required', new HashValidatorRule(new Programme())],
            'bailleurId' => ['sometimes', 'required', new HashValidatorRule(new Bailleur())],*/
            'organisationId' => ['sometimes', 'required', new HashValidatorRule(new Organisation())],
            'mois' => 'sometimes|required|integer',
            'annee' => 'sometimes|required',
            'debut' => 'sometimes|required|date|date_format:Y-m-d',
            'fin' => 'sometimes|required|date|date_format:Y-m-d|after_or_equal:debut',
            'ppm' => 'sometimes|required|min:1'
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
            'programmeId.required' => 'Le programme est obligatoire',
            'bailleurId.required' => 'Le bailleur est obligatoire',
            'mois.required' => 'Le mois est obligatoire',
            'annee.required' => 'L\'année est obligatoire',
            'debut.required' => 'La date de début est obligatoire',
            'debut.date' => 'La date de début doit etre une date',
            'debut.date_format' => 'La date doit être sous le format y-m-d',
            'fin.required' => 'La date de fin est obligatoire',
            'fin.date' => 'La date de fin doit etre une date',
            'fin.date_format' => 'La date de fin doit être sous le format y-m-d',
            'fin.after_or_equal' => 'La date de fin doit être supérieur à la date de début'
        ];
    }
}
