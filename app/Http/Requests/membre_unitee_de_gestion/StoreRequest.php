<?php

namespace App\Http\Requests\membre_unitee_de_gestion;

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
        $user = Auth::user();

        return $user->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'           => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'prenom'        => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'contact'       => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'email'         => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'poste'         => ['required','max:255']
        ];
    }
}
