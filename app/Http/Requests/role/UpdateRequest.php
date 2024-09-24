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
            'nom'  => ['required','max:255', Rule::unique('roles')->ignore($this->role)->whereNull('deleted_at')],
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
