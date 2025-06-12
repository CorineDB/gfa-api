<?php

namespace App\Http\Requests\user;

use App\Models\Programme;
use App\Models\Role;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'contact'               => ['required', 'max:14', Rule::unique('users', 'contact')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            'email'               => ['required', 'email', 'string', Rule::unique('users', 'email')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'roles'  => 'required|array|min:1',
            'roles.*'     => ['distinct', new HashValidatorRule(new Role())]
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
            'nom.max' => 'Le champ nom ne doit pas dépasser 255 caractères.',

            'prenom.required' => 'Le champ prénom est obligatoire.',
            'prenom.string' => 'Le champ prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le champ prénom ne doit pas dépasser 255 caractères.',

            'contact.required' => 'Le champ contact est obligatoire.',
            'contact.string' => 'Le champ contact doit être une chaîne de caractères.',
            'contact.max' => 'Le champ contact ne doit pas dépasser 12 caractères.',
            'contact.unique' => 'Ce contact est déjà utilisé dans ce programme.',

            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email doit être une adresse email valide.",
            'email.string' => "L'email doit être une chaîne de caractères.",
            'email.unique' => "Cet email est déjà utilisé dans ce programme.",

            'roles.required' => 'Au moins un rôle doit être sélectionné.',
            'roles.array' => 'Les rôles doivent être fournis sous forme de tableau.',
            'roles.min' => 'Au moins un rôle doit être attribué.',
            'roles.*.distinct' => 'Les rôles doivent être distincts.'
        ];
    }
}
