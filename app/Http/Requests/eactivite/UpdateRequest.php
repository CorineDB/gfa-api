<?php

namespace App\Http\Requests\eactivite;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\EntrepriseExecutant;
use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'nom' => 'sometimes|required',
            'debut' => 'sometimes|required|date|date_format:Y-m-d',
            'fin' => 'sometimes|required|date|date_format:Y-m-d|after_or_equal:debut',
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
            'code.required'                => 'Le code est obligatoire',
            'fin.required'         => 'La date de fin est obligatoire',
            'debut.required'                 => 'La date de début est obligatoire.',
            'nom.required'          => 'Le nom est obligatoire',
            'programmeId.required'               => 'Veuillez préciser le programme auquelle sera associé l\'activite .',
            'programmeId.exists'                 => 'Programme inconnu. Veuillez préciser un programme existant.',
            'entrepriseExecutantId.required'    => 'Veuillez préciser l\'entreprise executant .',
            'entrepriseExecutantId.exists'      => 'Entreprise executant inconnu. Veuillez préciser une entreprise executant existant.',
        ];
    }

}
