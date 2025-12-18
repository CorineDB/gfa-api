<?php

namespace App\Http\Requests\role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-un-role") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'               => ['required', 'string', Rule::unique('roles', 'nom')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

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
