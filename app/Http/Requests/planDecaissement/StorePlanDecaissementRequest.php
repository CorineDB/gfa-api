<?php

namespace App\Http\Requests\planDecaissement;

use App\Models\Activite;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StorePlanDecaissementRequest extends FormRequest
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
            'trimestre' => 'required|integer|min:1|max:4',
            'annee' => 'required|integer',
            'activiteId' => ['required',  new HashValidatorRule(new Activite())],
            'pret' => ['required', 'integer', 'min:0', function(){

                if($this->activiteId){
                    $activite = Activite::find($this->activiteId);
                    $pret = $activite->pret;
                    $totalPret = $activite->planDeDecaissements->sum('pret');

                    if(($totalPret + $this->pret) > $pret)
                    {
                        throw ValidationException::withMessages(["pret" => "Le total des prêts des plans de décaissement d'une activité ne peut pas dépasser celui de l'activité"], 1);
                    }
                }
            }],

            'budgetNational' => ['required', 'integer', 'min:0', function(){

                if($this->activiteId){
                    $activite = Activite::find($this->activiteId);
                    $budgetNational = $activite->budgetNational;
                    $totalBudgetNational = $activite->planDeDecaissements->sum('budgetNational');

                    if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                    {
                        throw ValidationException::withMessages(["budgetNational" => "Le total des bugdets nationaux des plans de décaissement d'une activité ne peut pas dépasser celui de l'activité"], 1);
                    }
                }
            }]
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
            'trimestre.required' => 'Le trimestre est obligatoire.',
            'trimestre.min' => 'La valeur minimal pour le trimestre est 1',
            'trimestre.max' => 'La valeur maximal pour le trimestre est 4',
            'annee.required' => 'L\'annee est obligatoire.',
            'budgetNational.required' => 'Le budget national est obligatoire.',
            'pret.required' => 'Le pret est obligatoire.',
            'activiteId.required' => 'L\'activité est obligatoire.'
        ];
    }

}
