<?php

namespace App\Http\Requests\statut;

use Illuminate\Foundation\Http\FormRequest;

class StatutStoreRequest extends FormRequest
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
            'etat' => 'required',
            'composanteId' => 'sometimes|required',
            'activiteId' => 'sometimes|required',
            'tacheId' => 'sometimes|required',
            'anoId' => 'sometimes|required'
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
            'etat.required' => 'L\'etat est obligatoire.',
        ];
    }
}
