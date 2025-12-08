<?php

namespace App\Http\Requests\role;

use App\Models\Permission;
use App\Rules\DecodeArrayHashIdRule;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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

            'description'       => 'required|max:255',
            'permissions'       => 'sometimes|required|array|min:1',
            'permissions.*'     => ['distinct', new DecodeArrayHashIdRule(new Permission())]
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = Auth::user();

        if( !(auth()->user()->hasRole("administrateur", "super-admin")) )
        { 
            $this->merge([
                'roleable_id' => $user->id,
                'roleable_type' => get_class($user)
            ]);
        }
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
            'description.required' => 'La description du role  est obligatoire.',
            'permissions.*.distinct' => 'Veuillez soumettre des permissions distinct.'
        ];
    }
}
