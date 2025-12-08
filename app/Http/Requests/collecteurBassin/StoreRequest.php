<?php

namespace App\Http\Requests\collecteurBassin;

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
            'nom' => 'required|unique:collecteur_bassins|max:255',
            'travaux' => 'required|max:255',
            'estimation' => 'required|integer',
            'engagement' => 'required|integer',
            'userId' => 'required'
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
            'nom.required' => 'Le nom du collecteur est obligatoire.',
            'travaux.required' => 'Les travaux sont obligatoire obligatoire.',
            'estimation.required' => 'L\'estimation est obligatoire est obligatoire.',
            'engagement.required' => 'L\'engagement est obligatoire.',
            'userId.required' => 'Veuillez préciser le bailleur associé au collecteur.'
        ];
    }
}
