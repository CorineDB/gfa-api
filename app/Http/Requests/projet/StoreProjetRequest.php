<?php

namespace App\Http\Requests\projet;

use App\Models\Bailleur;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Site;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreProjetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("creer-un-projet") || request()->user()->hasRole("unitee-de-gestion");
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
            'couleur' => 'required',
            'debut' => 'required|date|date_format:Y-m-d',
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut',
            'pays' => 'required|max:255',
            'bailleurId' => ['sometimes', 'nullable', new HashValidatorRule(new Bailleur())],
            'organisationId'   => ['sometimes', Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation()), function($attribute, $value, $fail) {
                if(request()->input($attribute)){
                    $organisation = Organisation::findByKey(request()->input($attribute));
                    if ($organisation->projet) {
                        $fail('Cette organisation est déja assigné a un projet du programme');
                    }
                }
            }],
            'nombreEmploie' => 'integer',
            'image' => ["file", 'mimes:jpg,png,jpeg,webp,svg,ico', "max:2048"],
            'fichier' => 'nullable|array',
            'fichier.*' => ["file", 'mimes:txt,doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv', "max:2048"],
            'budgetNational' => 'required|integer|min:0|max:9999999999999',
            'pret' => ['required', 'integer', 'min:0', 'max:9999999999999', function(){
                if($this->programmeId){
                    $programme = Programme::findByKey($this->programmeId);
                    $budgetNational = $programme->budgetNational;
                    $totalPret = $programme->projets->sum('pret');

                    if(($totalPret + $this->pret) > $budgetNational)
                    {
                        throw ValidationException::withMessages(["pret" => "Le total des montants de subvention alloues aux projets de ce programme ne peut pas dépasser le montant de la subvention du programme"], 1);
                    }
                }
            }],
            'sites'                         => ['required', 'array', 'min:1'],
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
            'budgetNational.required' => 'Le fond propre du projet est obligatoire.',
            'budgetNational.integer' => 'Le fond propre doit être un entier.',
            'budgetNational.max' => 'Le fond propre ne peut pas dépasser 9 999 999 999 999 CFA.',
            'description.required' => 'La description du projet est obligatoire.',
            'pret.required' => 'Le montant de la subvention du projet est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'bailleurId.required' => 'Le bailleur du projet est obligatoire.',
            'debut.required' => 'La date de début du programme est obligatoire.',
            'fin.required' => 'La date de fin du programme est obligatoire.',
        ];
    }
}
