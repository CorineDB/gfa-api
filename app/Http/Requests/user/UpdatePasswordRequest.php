<?php

namespace App\Http\Requests\user;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => ['required', function(){
                $user = Auth::user();
                if(!Hash::check($this->current_password, $user->password)){
                    throw ValidationException::withMessages(['current_password' => "Mot de passe incorrecte"]);
                }
            }],

            'new_password' => [
                'required',
                'min:8',
                'confirmed'
            ]
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
            'email.required'         => 'Veuillez préciser l\'email de l\'utilisateur.',
            'email.email'            => 'Format d\'email incorrect. Veuillez le corrigez.',
            'new_password.required' => 'Veuillez précisez votre nouveau mot de passe actuel',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
            'new_password.max' => 'Le nouveau mot de passe doit contenir au maximun 12 caractères',
            'new_password.confirmed' => 'Le mot de passe de confirmation doit correspondre au nouveau mot de passe',
        ];
    }
}
