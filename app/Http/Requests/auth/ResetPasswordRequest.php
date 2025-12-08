<?php

namespace App\Http\Requests\auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'token' => [ 'required', Rule::exists('users', 'token')],
            //'email' => 'required|email',
            'new_password' => [
                'required',
                Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised(),
                //'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
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
            'token.required'         => 'Veuillez préciser le token de réinitialisation du mot de passe.',
            'token.exists'           => 'Token invalid. Veuillez soumettre une nouvelle demande de réinitilisation de votre mot passe.',
            'email.required'         => 'Veuillez préciser l\'email de l\'utilisateur.',
            'email.email'            => 'Format d\'email incorrect. Veuillez le corrigez.',
            'new_password.required'  => 'Veuillez précisez votre nouveau mot de passe actuel',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
            'new_password.max' => 'Le nouveau mot de passe doit contenir au maximun 12 caractères',
            'new_password.confirmed' => 'Le mot de passe de confirmation doit correspondre au nouveau mot de passe',
        ];
    }
}
