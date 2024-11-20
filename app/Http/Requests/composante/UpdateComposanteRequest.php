<?php

namespace App\Http\Requests\composante;

use App\Models\Composante;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateComposanteRequest extends FormRequest
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

    public function prepareForValidation(){
        if(!is_object($this->composante))
        {
            if(($composante = Composante::findByKey($this->composante))){
                throw ValidationException::withMessages(["composante" =>"Composante Inconnue" ], 1);

            }

            $this->merge([
                "composante" => $composante->id
            ]);
        }
        else{
            $this->merge([
                "composante" => $this->composante->id
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'nom' => 'sometimes|required|max:255',
            'statut' => 'sometimes|required|integer|min:-1|max:-1',
            'poids' => ['sometimes', 'numeric', 'min:0'],
            'projetId' => ['sometimes',  Rule::requiredIf(!$this->composanteId), new HashValidatorRule(new Projet())],
            'composanteId' => ['sometimes',  Rule::requiredIf(!$this->projetId), new HashValidatorRule(new Composante())],
            
            'pret' => ['sometimes', 'integer', 'min:0', function(){
                if($this->projetId){
                    $projet = Projet::find($this->projetId);
                    if($projet){
                        $pret = $projet->pret;
                        $totalpret = $projet->composantes->sum('pret');

                        if(($totalpret + $this->pret) > $pret)
                        {
                            throw ValidationException::withMessages(["pret" => "Le total des budgets alloues aux outcomes de ce projet ne peuvent pas dépasser le montant du budget alloue au projet"], 1);
                        }
                    }
                }

                elseif($this->composanteId){
                    $composante = Composante::find($this->composanteId);
                    if($composante){
                        $pret = $composante->pret;
                        $totalpret = $composante->sousComposantes->sum('pret');

                        if(($totalpret + $this->pret) > $pret)
                        {
                            throw ValidationException::withMessages(["pret" => "Le total des budgets alloues aux outputs de cet outcome ne peuvent pas dépasser le montant du budget alloue a l'outcome"], 1);
                        }
                    }
                }
            }],

            'budgetNational' => ['sometimes', 'integer', 'min:0', function(){
                if($this->projetId){
                    $projet = Projet::find($this->projetId);
                    if($projet){
                        $budgetNational = $projet->budgetNational;
                        $totalBudgetNational = $projet->composantes->sum('budgetNational');

                        if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                        {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des fonds propres aux outcomes de ce projet ne peut pas dépasser le montant du fond propre du projet"], 1);
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
                            throw ValidationException::withMessages(["budgetNational" => "Le total des fonds propres des outputs de cet outcome ne peuvent pas dépasser le montant du fond propre de l'outcome"], 1);
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
            'statut.required' => 'Le statut de la composante est obligatoire.',
            'poids.required' => 'Le poids de la composante est obligatoire.',
            'budjetNational.required' => 'Le budget national de la composante est obligatoire.',
            'pret.required' => 'Le pret effectué de la composante est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de la composante est obligatoire.',
            'projetId.required' => 'Le projet de la composante est obligatoire.',
            'projetId.exists' => 'Ce projet n\'existe pas',
            'composanteId.required' => 'La sous composante  est obligatoire.',
        ];
    }
}
