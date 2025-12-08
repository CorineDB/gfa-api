<?php

namespace App\Http\Requests\unitee_de_gestion;

use App\Models\Programme;
use App\Models\UniteeDeGestion;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-une-unitee-de-gestion") || request()->user()->hasRole("administrateur", "super-admin");
    }

    /*
        protected function prepareForValidation(): void
        {

            if($this->programmeId){

                $programme = Programme::findByKey($this->programmeId);

                if(!isset($programme->id))
                    throw ValidationException::withMessages(['programmeId' => "Programme inconnu"]);

                if(is_string($this->unitee_de_gestion))
                {
                    $this->unitee_de_gestion = UniteeDeGestion::findByKey($this->unitee_de_gestion);
                }

                if( isset($programme->uniteDeGestion) ){
                    if( $programme->uniteDeGestion->id !== $this->unitee_de_gestion->id ) throw ValidationException::withMessages(['programmeId' => "Ce programme a déjà un compte pour l'administrateur de l'unité de gestion."]);
                }

                $this->merge([
                    'programmeId' => $programme->id
                ]);
            }
        }
    */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'nom'                       => 'sometimes|max:255',
            'contact'                   => ['sometimes', 'min:8', 'max:24', Rule::unique('users')->ignore($this->unitee_de_gestion->user)->whereNull('deleted_at')],
            'email'                     => ['sometimes','email','max:255', Rule::unique('users')->ignore($this->unitee_de_gestion->user)->whereNull('deleted_at')],
            'programmeId'               => ['sometimes', new HashValidatorRule(new Programme()), function(){

                if(is_string($this->unitee_de_gestion))
                {
                    $this->unitee_de_gestion = UniteeDeGestion::findByKey($this->unitee_de_gestion);
                }

                $programme = Programme::findByKey($this->programmeId);

                if( isset($programme->uniteDeGestion) ){
                    if(  optional($programme->uniteDeGestion)->id !==  optional($this->unitee_de_gestion)->id ) throw ValidationException::withMessages(['programmeId' => "Ce programme a déjà un compte pour l'administrateur de l'unité de gestion."]);
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
            "email.email" => "adresse email invalide. Veuillez saissir une adresse email correcte",
        ];
    }
}
