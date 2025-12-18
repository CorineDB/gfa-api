<?php

namespace App\Http\Requests\eActiviteMod;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'description' => 'required',
            'debut' => 'required|date|date_format:Y-m-d',
            'fin' => 'required|date|date_format:Y-m-d',
            'statut' => 'required',
            'siteId' => 'required',
            'bailleurId' => 'required',
            'programmeId' => 'required',
            'modId' => 'required'
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
            'description.required' => 'La description est obligatoire',
            'debut.required' => 'La date de début est obligatoire',
            'debut.forma_format' => 'La date de début doit être sous le format : annee-mois-jour',
            'fin.required' => 'La date de fin est obligatoire',
            'fin.forma_format' => 'La date de fin doit être sous le format : annee-mois-jour',
            'statut.required' => 'Le statut est obligatoire',
            'siteId.required' => 'Le site est obligatoire',
            'bailleurId.required' => 'Le bailleur est obligatoire',
            'programmeId.required' => 'Le programme est obligatoire',
            'modId.required' => 'Le mod est obligatoire'
        ];
    }
}
