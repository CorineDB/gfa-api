<?php

namespace App\Http\Requests\planDecaissement;

use App\Models\Activite;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdatePlanDecaissementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-un-plan-de-decaissement") || request()->user()->hasRole("unitee-de-gestion");

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
            'pret' => ['required', 'integer', 'min:0', 'max:9999999999999', function(){

                if($this->activiteId){
                    $activite = Activite::find($this->activiteId);
                    $pret = $activite->pret;
                    $totalPret = $activite->planDeDecaissements->where("id", "!=", $this->planDecaissement)->sum('pret');

                    if(($totalPret + $this->pret) > $pret)
                    {
                        throw ValidationException::withMessages(["pret" => "Le total des montants de subvention des plans de décaissement de cette activité ne peut pas dépasser le montant de la subvention de l'activité"], 1);
                    }
                }
            }],

            'budgetNational' => ['required', 'integer', 'min:0', 'max:9999999999999', function(){

                if($this->activiteId){
                    $activite = Activite::find($this->activiteId);
                    $budgetNational = $activite->budgetNational;
                    $totalBudgetNational = $activite->planDeDecaissements->where("id", "!=", $this->planDecaissement)->sum('budgetNational');

                    if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                    {
                        throw ValidationException::withMessages(["budgetNational" => "Le total des fonds propres des plans de décaissement de cette activité ne peut pas dépasser le fond propre de l'activité"], 1);
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
            'budgetNational.required' => 'Le fond propre est obligatoire.',
            'budgetNational.integer' => 'Le fond propre doit être un entier.',
            'budgetNational.max' => 'Le fond propre ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le montant de la subvention est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'activiteId.required' => 'L\'activité est obligatoire.'
        ];
    }
}
