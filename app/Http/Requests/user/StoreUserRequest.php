<?php

namespace App\Http\Requests\user;

use App\Models\Programme;
use App\Models\Role;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-un-utilisateur") || request()->user()->hasRole("administrateur", "super-admin", "unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'contact' => 'nullable|max:12|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'roles'  => 'required|array|min:1',
            'roles.*'     => ['distinct', new HashValidatorRule(new Role())],
            'programmeId'               => ['sometimes', new HashValidatorRule(new Programme()), function(){

                $programme = Programme::findByKey($this->programmeId);

                if( $programme->id != auth()->user()->programmeId ){
                    throw ValidationException::withMessages(['programmeId' => "L'utilisateur doit appartenir au même programme que le chef equipe"]);
                }

            }]
        ];
    }
}
