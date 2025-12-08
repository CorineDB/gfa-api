<?php

namespace App\Http\Requests\suivi;

use App\Models\Tache;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreSuiviV2Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-un-suivi") || request()->user()->hasRole("unitee-de-gestion", "organisation");

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->id){
           $tache = Tache::findByKey($this->id);

            if( $tache->statut && !($tache->statut >= 0 &&  $tache->statut < 2) ){
                throw ValidationException::withMessages(["tacheId" =>  "Le suivi ne peut qu'etre effectuer, que pour les tâches en cours ou en retard d'execution."]);
            }
        }
        
        return [
            'commentaire'       => 'sometimes',

            'poidsActuel'       => ["required", "integer", "in:0,50,100", ],
            'tacheId'           => ['sometimes',  new HashValidatorRule(new Tache()), function(){

                $tache = Tache::findByKey($this->tacheId);

                if( $tache->statut && !($tache->statut >= 0 &&  $tache->statut < 2) ){
                    throw ValidationException::withMessages(["tacheId" =>  "Le suivi ne peut qu'etre effectuer, que pour les tâches en cours ou en retard d'execution."]);
                }
            }],
            'date'              => 'required|date|date_format:Y-m-d'
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
            'poidsActuel.required' => 'Le poids est obligatoire.',
        ];
    }
}
