<?php

namespace App\Http\Requests\activite;

use App\Models\Composante;
use App\Models\Projet;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreActiviteRequest extends FormRequest
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
            'nom' => 'required',
            'poids' => 'required',
            'debut' => 'required|date|date_format:Y-m-d',
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut',
            'type' => 'required|max:255',
            'userId' => ['required', new HashValidatorRule(new User())],
            'composanteId' => ['required', new HashValidatorRule(new Composante())],
            'structureResponsableId' => ['required', new HashValidatorRule(new User())],
            'structureAssocieId' => ['required', new HashValidatorRule(new User())],

            'pret' => ['required', 'integer', 'min:0', function(){

                if($this->composanteId){
                    $composante = Composante::find($this->composanteId);
                    if($composante){

                        $pret = $composante->pret;
                        $totalPret = $composante->activites->sum('pret');

                        if(($totalPret + $this->pret) > $pret)
                        {
                            throw ValidationException::withMessages(["pret" => "Le total des prêts des activites ne peut pas dépasser le montant du pret de la composante. "], 1);
                        }
                    }
                }
            }],

            'budgetNational' => ['required', 'integer', 'min:0', function(){
                if($this->composanteId){
                    $composante = Composante::find($this->composanteId);
                    if($composante){
                        $budgetNational = $composante->budgetNational;
                        $totalBudgetNational = $composante->activites->sum('budgetNational');

                        if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                        {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des budgets nationaux des activites ne peut pas dépasser le montant du budget national de la composante."], 1);
                        }
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
            'nom.required' => 'Le nom de l\'activité est obligatoire.',
            'poids.required' => 'Le poids de l\'activité est obligatoire.',
            'debut.required' => 'La date de debut de l\'activité est obligatoire.',
            'fin.required' => 'La date de fin de l\'activité est obligatoire.',
            'type.required' => 'Le type de l\'activité est obligatoire.',
            'budjetNational.required' => 'Le budget national de l\'activité est obligatoire.',
            'pret.required' => 'Le pret effectué de l\'activité est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de l\'activité est obligatoire.',
            'userId.required' => 'Le responsable de l\'activité est obligatoire',
            'structureResponsableId.required' => 'La structure responsable de l\'activité est obligatoire',
            'structureAssocieId.required' => 'La structure associé de l\'activité est obligatoire',
        ];
    }

}
