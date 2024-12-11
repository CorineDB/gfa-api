<?php

namespace App\Http\Requests\suivi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuiviRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("modifier-un-suivi") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'poidsActuel'             => 'required|integer|in:0,50,100'
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
            'poidsActuel.required' => 'Le poids est obligatoire.',
        ];
    }
}
