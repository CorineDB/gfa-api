<?php

namespace App\Http\Requests\unitee_de_gestion;

use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-une-unitee-de-gestion") || request()->user()->hasRole("administrateur", "super-admin");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'                       => ['required','max:255'],
            'contact'                   => ['required', 'numeric','digits_between:8,24', Rule::unique('users')->whereNull('deleted_at')],
            'email'                     => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'programmeId'               => ['required', new HashValidatorRule(new Programme()), function(){


                $programme = Programme::findByKey($this->programmeId);
                
                if( isset($programme->uniteDeGestion) ){
                    throw ValidationException::withMessages(['programmeId' => "Ce programme a déjà un compte pour l'administrateur de l'unité de gestion."]);
                }

            }],
        ];
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return
        [
            "contact.min" => "Le numéro de téléphone est incorrecte. Veuillez saissir un numéro valide",
            "contact.max" => "Le numéro de téléphone est incorrecte. Veuillez saissir un numéro valide",
            "email.email" => "adresse email invalide. Veuillez saissir une adresse email correcte"
        ];
    }
}
