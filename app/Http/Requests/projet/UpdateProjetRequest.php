<?php

namespace App\Http\Requests\projet;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\Site;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
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
        return request()->user()->hasPermissionTo("modifier-un-projet") || request()->user()->hasRole("unitee-de-gestion", "organisation");
        return request()->user()->hasRole("unitee-de-gestion") || request()->user()->hasRole("organisation");
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
            'pays' => 'sometimes|max:255',
            'organisationId' => ['sometimes', new HashValidatorRule(new Organisation())],
            //'bailleurId' => ['sometimes','required', new HashValidatorRule(new Bailleur())],
            'nombreEmploie' => 'sometimes|integer',
            'image' => ["nullable", "file", 'mimes:jpg,png,jpeg,webp,svg,ico', "max:2048"],
            'fichier' => 'nullable|array',
            'fichier.*' => ["nullable", "file", 'mimes:txt,doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv', "max:2048"],


            'pret' => ['sometimes', 'integer', 'min:0', function(){
                // Vérification vers le PARENT (programme)
                if($this->programmeId){
                    $programme = Programme::findByKey($this->programmeId);
                    $budgetNational = $programme->budgetNational;
                    $totalBudgetNational = $programme->projets->sum('pret');

                    if(($totalBudgetNational + $this->budgetNational) > $budgetNational)
                    {
                        throw ValidationException::withMessages(["budgetNational" => "Le total des fonds alloues aux projets de ce programme ne peut pas dépasser le montant du fond alloue au programme"], 1);
                    }
                }

                // Vérification vers les ENFANTS (composantes/outcomes)
                if($this->route('projet')){
                    $projet = Projet::findByKey($this->route('projet'));
                    if($projet){
                        $totalpretComposantes = $projet->composantes->sum('pret');

                        if($this->pret < $totalpretComposantes)
                        {
                            throw ValidationException::withMessages(["pret" => "Le montant du budget alloue au projet ne peut pas etre inferieur au total des budgets alloues aux outcomes de ce projet."], 1);
                        }
                    }
                }
            }],
            'budgetNational' => ['sometimes', 'integer', 'min:0', function(){
                // Vérification vers les ENFANTS (composantes/outcomes)
                if($this->route('projet')){
                    $projet = Projet::findByKey($this->route('projet'));
                    if($projet){
                        $totalBudgetNationalComposantes = $projet->composantes->sum('budgetNational');

                        if($this->budgetNational < $totalBudgetNationalComposantes)
                        {
                            throw ValidationException::withMessages(["budgetNational" => "Le montant du fond propre alloue au projet ne peut pas etre inferieur au total des fonds propres alloues aux outcomes de ce projet."], 1);
                        }
                    }
                }
            }],
            'sites'                         => ['sometimes', 'array', 'min:1'],
            'sites.*'                       => ['distinct', new HashValidatorRule(new Site())]
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
