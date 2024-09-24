<?php

namespace App\Http\Requests\tache;

use App\Models\Activite;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreTacheRequest extends FormRequest
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
            'nom' => 'required',
            //'statut' => 'required|integer|min:-2|max:2',
            'poids' => 'required',
            'debut' => 'required|date|date_format:Y-m-d',
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut',
            'activiteId' => ['required', new HashValidatorRule(new Activite())],
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
            'nom.required' => 'Le nom de la tache est obligatoire.',
            //'statut.required' => 'Le statut de la tache est obligatoire.',
            'poids.required' => 'Le poids de la tache est obligatoire.',
            'debut.required' => 'La date de debut de la tache est obligatoire.',
            'fin.required' => 'La date de fin de la tache est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de la tache est obligatoire.',
            'activiteId.required' => 'L\'activité est obligatoire'
        ];
    }
}
