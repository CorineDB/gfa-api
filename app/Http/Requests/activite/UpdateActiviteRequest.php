<?php

namespace App\Http\Requests\activite;

use App\Models\Activite;
use App\Models\Composante;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateActiviteRequest extends FormRequest
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

    public function prepareForValidation(){
        if(!is_object($this->activite))
        {
            if(($activite = Activite::findByKey($this->activite))){
                throw ValidationException::withMessages(["activite" =>"Activite Inconnue" ], 1);
            }

            $this->merge([
                "activite" => $activite->id
            ]);
        }
        else{
            $this->merge([
                "activite" => $this->activite->id
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
            'poids' => 'sometimes|required',
            'type' => 'sometimes|required|max:255',
            'composanteId' => ['required', new HashValidatorRule(new Composante())],
            'userId' => ['sometimes', 'required', new HashValidatorRule(new User())],
            'description' => 'string',
            //'structureResponsableId' => ['sometimes', 'required', new HashValidatorRule(new User())],
            //'structureAssocieId' => ['sometimes', 'required', new HashValidatorRule(new User())],

            'budgetNational' => ['sometimes','required', 'integer', 'min:0', function(){
                if($this->composanteId){
                    $composante = Composante::find($this->composanteId);
                    $budgetNational = $composante->budgetNational;
                    $totalBudgetNational = $composante->activites->where("id", "!=", $this->activite)->sum('budgetNational');

                    if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                    {
                        throw ValidationException::withMessages(["budgetNational" => "Le total des budgets nationaux des activites ne peut pas dépasser le montant du budget national de la composante"], 1);
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
            'statut.required' => 'Le statut de l\'activité est obligatoire.',
            'poids.required' => 'Le poids de l\'activité est obligatoire.',
            'type.required' => 'Le type de l\'activité est obligatoire.',
            'budjetNational.required' => 'Le budget national de l\'activité est obligatoire.',
            'pret.required' => 'Le pret effectué de l\'activité est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de l\'activité est obligatoire.',
            'userId.required' => 'Le responsable de l\'activité est obligatoire',
            'structureResponsableId.required' => 'La structure responsable de l\'activité est obligatoire',
            'structureAssocieId.required' => 'La structure associé de l\'activité est obligatoire',
            'composanteId.required' => 'La composante de l\'activité est obligatoire',
        ];
    }
}
