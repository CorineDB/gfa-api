<?php

namespace App\Http\Requests\user;

use App\Models\Role;
use Illuminate\Validation\Rule;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-un-utilisateur") || request()->user()->hasRole("administrateur", "super-admin", "unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'contact'               => ['sometimes', 'string','max:12', Rule::unique('users', 'contact')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            'email'               => ['sometimes', 'email', 'string', Rule::unique('users', 'email')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            'roles'  => 'sometimes|array|min:1',
            'roles.*'     => ['distinct', new HashValidatorRule(new Role())]
        ];
    }
}
