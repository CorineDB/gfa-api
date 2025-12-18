<?php

namespace App\Http\Requests\mission_de_controle;

use App\Models\Bailleur;
use App\Rules\HashValidatorRule;
use App\Models\MissionDeControle;
use App\Models\Programme;
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
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'nom'                       => ['required','max:255', Rule::unique('users','nom')->ignore($this->mission_de_controle->user)->whereNull('deleted_at')],
            'contact'                   => ['required','max:255', Rule::unique('users')->ignore($this->mission_de_controle->user)->whereNull('deleted_at')],
            'email'                     => ['required','email','max:255', Rule::unique('users')->ignore($this->mission_de_controle->user)->whereNull('deleted_at')],
            'programmeId'               => ['required', new HashValidatorRule(new Programme()), function(){

                if(is_string($this->mission_de_controle))
                {
                    $this->mission_de_controle = MissionDeControle::findByKey($this->mission_de_controle);
                }

                $programme = Programme::findByKey($this->programmeId);

                if( isset($programme->userMissionDeControle->missionDeControle) ){
                    if( $programme->userMissionDeControle->missionDeControle->id !== $this->mission_de_controle->id ) throw ValidationException::withMessages(['programmeId' => "Ce programme a déjà un compte pour l'administrateur de la mission de contrôle."]);
                }
            }],
            'bailleurId'               => ['required', new HashValidatorRule(new Bailleur())]
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
            'nom.required'                      => 'Veuillez préciser votre nom.',
            'contact.required'                  => 'Veuillez préciser votre numéro de téléphone.',
            'email.email'                       => 'Veuillez préciser une adresse email valide.',
            'email.unique'                      => 'Une adresse email avait déjà été enrégistré.',
            'programmeId.required'              => 'Veuillez préciser le programme auquelle sera associé la mission de controle.',
            'programmeId.exists'                => 'Programme inexistant. Veuillez préciser un programme existant.'
        ];
    }
}
