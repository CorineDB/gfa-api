<?php

namespace App\Http\Requests\role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
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
            'nom' => 'required|max:255|unique:roles,nom',
            'description' => 'required|max:255'
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
            'nom.required' => 'Le nom du role  est obligatoire.',
            'nom.unique' => 'Rôle déjà enrégistré.',
            'description.required' => 'La description du role  est obligatoire.'
        ];
    }
}
