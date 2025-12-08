<?php

namespace App\Http\Requests\evalautions;

use App\Models\Programme;
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
            'intitule' => 'required|max:255|unique:evaluations,intitule',
            'objectif_attendu' => 'required|integer|min:0',
            'annee_exercice' => 'required|integer',
            'description' => 'nullable|max:255',
            'debut' => 'required|date|date_format:Y-m-d',
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut'
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
            // Custom messages for the 'nom' field
            'nom.required'      => 'Le champ nom est obligatoire.',
            'nom.max'           => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique'        => 'Ce nom est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'principeDeGouvernanceId' field
            'principeDeGouvernanceId.required' => 'Le champ principe de gouvernance est obligatoire.',
        
        ];
    }
}
