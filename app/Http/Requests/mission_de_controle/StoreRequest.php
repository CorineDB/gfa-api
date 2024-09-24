<?php

namespace App\Http\Requests\mission_de_controle;

use App\Models\Bailleur;
use App\Models\Programme;
use App\Rules\HashValidatorRule;
use App\Models\MissionDeControle;
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
            'nom'                       => ['required','max:255', Rule::unique('users','nom')->whereNull('deleted_at')],
            'contact'                   => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'email'                     => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'bailleurId'               => ['required', new HashValidatorRule(new Bailleur())],
            'programmeId'               => ['required', new HashValidatorRule(new Programme()), function(){

                $programme = Programme::findByKey($this->programmeId);
                $bailleur = Bailleur::findByKey($this->bailleurId);

                if( isset($bailleur->missionDeControle) ){
                    if( optional( $bailleur->missionDeControle)->profilable_id !== optional($this->mission_de_controle)->id ) throw ValidationException::withMessages(['programmeId' => "Ce programme a déjà un compte pour l'administrateur de la mission de contrôle."]);
                }
            }],

        ];
    }
}
