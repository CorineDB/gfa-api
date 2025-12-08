<?php

namespace App\Http\Requests\role;

use App\Models\Permission;
use App\Models\Role;
use App\Rules\DecodeArrayHashIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("modifier-un-role") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'               => ['sometimes', 'string', Rule::unique('roles', 'nom')->ignore($this->role)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'description' => 'required|max:255',
            'permissions'       => 'sometimes|required|array|min:1',
            'permissions.*'     => ['distinct', new DecodeArrayHashIdRule(new Permission())]
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
            'nom.required' => 'Le nom du role est obligatoire.',
            'nom.unique' => 'Rôle déjà enrégistré.',
            'description.required' => 'La description du role  est obligatoire.',
        ];
    }

}
