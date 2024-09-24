<?php

namespace App\Http\Requests\composante;

use App\Models\Composante;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\ValidationException;

class StoreComposanteRequest extends FormRequest
{
    private $user;

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
            'poids' => ['required'],
            'projetId' => [ Rule::requiredIf(!$this->composanteId), 'bail', new HashValidatorRule(new Projet())],
            'composanteId' => [ Rule::requiredIf(!$this->projetId), 'bail', new HashValidatorRule(new Composante())],

            'pret' => ['required', 'integer', 'min:0', function(){

                if($this->projetId){
                    $projet = Projet::find($this->projetId);
                    if($projet){
                        $pret = $projet->pret;
                        $totalPret = $projet->composantes->sum('pret');

                        if(($totalPret + $this->pret) > $pret)
                        {
                            throw ValidationException::withMessages(["pret" => "Le total des prêts des composantes de ce projet ne peut pas dépasser le montant du pret du projet"], 1);

                        }
                    }
                }

                elseif($this->composanteId){
                    $composante = Composante::find($this->composanteId);
                    if($composante){
                        $pret = $composante->pret;
                        $totalPret = $composante->sousComposantes->sum('pret');

                        if(($totalPret + $this->pret) > $pret)
                        {
                            throw ValidationException::withMessages(["pret" => "Le total des prêts des sous composantes de cette composante ne peut pas dépasser le montant du pret de la composante"], 1);
                        }
                    }
                }
            }],
            'budgetNational' => ['required', 'integer', 'min:0', function(){
                if($this->projetId){
                    $projet = Projet::find($this->projetId);
                    if($projet){
                        $budgetNational = $projet->budgetNational;
                        $totalBudgetNational = $projet->composantes->sum('budgetNational');

                        if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                        {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des budgets nationaux des composantes de ce projet ne peut pas dépasser le montant du budget national du projet"], 1);
                        }
                    }
                }

                elseif($this->composanteId){
                    $composante = Composante::find($this->composanteId);
                    if($composante){
                        $budgetNational = $composante->budgetNational;
                        $totalBudgetNational = $composante->sousComposantes->sum('budgetNational');

                        if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                        {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des budgets nationaux des sous composantes de cette composante ne peut pas dépasser le montant du budget national de la composante"], 1);
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
            'nom.required' => 'Le nom de la composante est obligatoire.',
            'poids.required' => 'Le poids de la composante est obligatoire.',
            'budjetNational.required' => 'Le budget national de la composante est obligatoire.',
            'pret.required' => 'Le pret effectué de la composante est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de la composante est obligatoire.',
            'projetId.required' => 'Le projet est obligatoire.',
            'composanteId.required' => 'La composante de la sous composante est obligatoire.'
        ];
    }
}
