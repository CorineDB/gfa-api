<?php

namespace App\Http\Requests\projet;

use App\Models\Bailleur;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateProjetRequest extends FormRequest
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
            'nom' => 'sometimes|required|max:255',
            'statut' => 'sometimes|required|integer|min:-1|max:-1',
            'couleur' => 'sometimes|required',
            'debut' => 'sometimes|required|date|date_format:Y-m-d',
            'fin' => 'sometimes|required|date|date_format:Y-m-d|after_or_equal:debut',
            'ville' => 'sometimes|required|max:255',
            'budgetNational' => 'sometimes|required|integer|min:0',
            'pret' => 'sometimes|required|integer|min:0',
            'organisationId' => ['sometimes', new HashValidatorRule(new Organisation())],
            //'bailleurId' => ['sometimes','required', new HashValidatorRule(new Bailleur())],
            'nombreEmploie' => 'sometimes|integer',
            'image' => 'nullable|mimes:jpg,png,jpeg,webp,svg,ico|max:2048',

            'budgetNational' => ['sometimes', 'required', 'integer', 'min:0', function(){
                if($this->programmeId){
                    $projet = Projet::findByKey($this->id);
                    $budgetNational = Auth::user()->programme->budgetNational;
                    $totalBudgetNational = Auth::user()->programme->projets->where("id", "!=", $projet->id)->sum('budgetNational');
                    if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                    {
                        throw ValidationException::withMessages(["budgetNational" => "Le total des budgets nationaux des projets de ce programme ne peut pas dépasser le montant du budget national du programme"], 1);
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
            'nom.required' => 'Le nom du projet est obligatoire.',
            'statut.required' => 'Le statut du projet est obligatoire.',
            'couleur.required' => 'La couleur du projet est obligatoire.',
            'poids.required' => 'Le poids du projet est obligatoire.',
            'ville.required' => 'La ville du projet est obligatoire.',
            'budjetNational.required' => 'Le budget national du projet est obligatoire.',
            'pret.required' => 'Le pret effectué du projet est obligatoire.',
            'bailleurId.required' => 'Le bailleur du projet est obligatoire.',
            'programmeId.required' => 'Le programme du projet est obligatoire.',
            'debut.required' => 'La date de début du programme est obligatoire.',
            'fin.required' => 'La date de fin du programme est obligatoire.',
        ];
    }
}
