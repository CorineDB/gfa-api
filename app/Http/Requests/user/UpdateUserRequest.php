<?php

namespace App\Http\Requests\user;

use App\Models\Role;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo('modifier-un-utilisateur') || request()->user()->hasRole('administrateur', 'super-admin', 'unitee-de-gestion', 'organisation');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Récupérer l'ID utilisateur depuis les paramètres de route (supporte 'id' et 'utilisateur')
        $userId = $this->route('id') ?? $this->route('utilisateur');

        if (is_string($userId)) {
            $user = User::findByKey($userId);
            $userId = $user ? $user->id : null;
        }

        return [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'contact' => ['sometimes', 'max:14', Rule::unique('users', 'contact')->where('programmeId', auth()->user()->programmeId)->ignore($userId)->whereNull('deleted_at')],
            'email' => ['sometimes', 'email', 'string', Rule::unique('users', 'email')->where('programmeId', auth()->user()->programmeId)->ignore($userId)->whereNull('deleted_at')],
            'roles' => 'sometimes|array|min:1',
            'roles.*' => ['distinct', new HashValidatorRule(new Role())]
        ];
    }

    public function messages()
    {
        return [
            'nom.sometimes' => 'Le champ nom est requis uniquement.',
            'nom.string' => 'Le nom doit être une chaîne de caractères valide.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'prenom.sometimes' => 'Le champ prénom est requis uniquement.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères valide.',
            'prenom.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            'contact.sometimes' => 'Le champ contact est requis uniquement.',
            'contact.string' => 'Le contact doit être une chaîne de caractères valide.',
            'contact.max' => 'Le contact ne peut pas dépasser 12 caractères.',
            'contact.unique' => 'Ce contact est déjà utilisé pour ce programme.',
            'contact.where' => "Le contact doit être associé au même programme que l'utilisateur.",
            'email.sometimes' => 'Le champ email est requis uniquement.',
            'email.email' => "L'email doit être une adresse email valide.",
            'email.string' => "L'email doit être une chaîne de caractères valide.",
            'email.unique' => 'Cet email est déjà utilisé pour ce programme.',
            'email.where' => "L'email doit être associé au même programme que l'utilisateur.",
            'roles.sometimes' => 'Le champ rôles est requis uniquement.',
            'roles.array' => 'Les rôles doivent être fournis sous forme de tableau.',
            'roles.min' => 'Au moins un rôle doit être attribué.',
            'roles.*.distinct' => 'Les rôles doivent être distincts.',
        ];
    }
}
