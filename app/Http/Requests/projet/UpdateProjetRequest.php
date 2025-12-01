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


            'pret' => ['sometimes', 'integer', 'min:0', 'max:9999999999999', function () {
                // Vérification vers le PARENT (programme)
                if ($this->programmeId) {
                    $programme = Programme::findByKey($this->programmeId);
                    $budgetNational = $programme->budgetNational;
                    $totalPret = $programme->projets->where('id', '!=', $this->route('projet') ? Projet::findByKey($this->route('projet'))->id : null)->sum('pret');

                    if (($totalPret + $this->pret) > $budgetNational) {
                        throw ValidationException::withMessages(["pret" => "Le total des montants de subvention alloues aux projets de ce programme ne peut pas dépasser le montant de la subvention du programme"], 1);
                    }
                }

                // Vérification vers les ENFANTS (composantes/outcomes)
                if ($this->route('projet')) {
                    $projet = Projet::findByKey($this->route('projet'));
                    if ($projet) {
                        $totalpretComposantes = $projet->composantes->sum('pret');

                        if ($this->pret < $totalpretComposantes) {
                            throw ValidationException::withMessages(["pret" => "Le montant de la subvention alloue au projet ne peut pas etre inferieur au total des subventions allouees aux outcomes de ce projet."], 1);
                        }
                    }
                }
            }],
            'budgetNational' => ['sometimes', 'integer', 'min:0', 'max:9999999999999', function () {
                // Vérification vers les ENFANTS (composantes/outcomes)
                if ($this->route('projet')) {
                    $projet = Projet::findByKey($this->route('projet'));
                    if ($projet) {
                        $totalBudgetNationalComposantes = $projet->composantes->sum('budgetNational');

                        if ($this->budgetNational < $totalBudgetNationalComposantes) {
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
            'budgetNational.required' => 'Le fond propre du projet est obligatoire.',
            'budgetNational.integer' => 'Le fond propre doit être un entier.',
            'budgetNational.max' => 'Le fond propre ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le montant de la subvention du projet est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'bailleurId.required' => 'Le bailleur du projet est obligatoire.',
            'programmeId.required' => 'Le programme du projet est obligatoire.',
            'debut.required' => 'La date de début du programme est obligatoire.',
            'fin.required' => 'La date de fin du programme est obligatoire.',
        ];
    }
    public function messages()
    {
        return [
            'nom.required' => 'Le :attribute est obligatoire.',
            'nom.max' => 'Le :attribute ne peut pas dépasser 255 caractères.',

            'statut.required' => 'Le :attribute est obligatoire.',
            'statut.integer' => 'Le :attribute doit être un entier.',
            'statut.min' => 'Le :attribute est invalide.',
            'statut.max' => 'Le :attribute est invalide.',

            'couleur.required' => 'La :attribute est obligatoire.',

            'debut.required' => 'La :attribute est obligatoire.',
            'debut.date' => 'La :attribute doit être une date valide.',
            'debut.date_format' => 'La :attribute doit respecter le format YYYY-MM-DD.',

            'fin.required' => 'La :attribute est obligatoire.',
            'fin.date' => 'La :attribute doit être une date valide.',
            'fin.date_format' => 'La :attribute doit respecter le format YYYY-MM-DD.',
            'fin.after_or_equal' => 'La :attribute doit être postérieure ou égale à la date de début.',

            'pays.max' => 'Le :attribute ne peut pas dépasser 255 caractères.',

            'organisationId.required' => 'L\':attribute est obligatoire.',

            'nombreEmploie.integer' => 'Le :attribute doit être un entier.',

            'image.file' => 'Le :attribute doit être un fichier.',
            'image.mimes' => 'Le :attribute doit être de type :values.',
            'image.max' => 'Le :attribute ne peut pas dépasser 2 Mo.',

            'fichier.array' => 'Les :attribute doivent être une liste de fichiers.',
            'fichier.*.file' => 'Chaque fichier doit être un fichier valide.',
            'fichier.*.mimes' => 'Chaque fichier doit être de type :values.',
            'fichier.*.max' => 'Chaque fichier ne peut pas dépasser 2 Mo.',

            'budgetNational.required' => 'Le montant du :attribute est obligatoire.',
            'budgetNational.integer' => 'Le montant du :attribute doit être un entier.',
            'budgetNational.min' => 'Le montant du :attribute doit être supérieur ou égal à 0.',
            'budgetNational.max' => 'Le montant du :attribute ne peut pas dépasser 9 999 999 999 999 CFA.',

            'pret.required' => 'Le montant de la :attribute est obligatoire.',
            'pret.integer' => 'Le montant de la :attribute doit être un entier.',
            'pret.min' => 'Le montant de la :attribute doit être supérieur ou égal à 0.',
            'pret.max' => 'Le montant de la :attribute ne peut pas dépasser 9 999 999 999 999 CFA.',

            'sites.required' => 'Les :attribute sont obligatoires.',
            'sites.array' => 'Les :attribute doivent être une liste.',
            'sites.min' => 'Vous devez sélectionner au moins un :attribute.',
            'sites.*.distinct' => 'Les :attribute doivent être uniques.',
        ];
    }

    public function attributes()
    {
        return [
            'nom' => 'nom du projet',
            'statut' => 'statut du projet',
            'couleur' => "couleur d'identification du projet",
            'debut' => 'date de début',
            'fin' => 'date de fin',
            'pays' => 'pays',
            'organisationId' => 'organisation porteuse',
            'nombreEmploie' => 'nombre d’emplois',
            'image' => 'image du projet',
            'fichier' => 'fichiers du projet',
            'budgetNational' => 'fond propre',
            'pret' => 'subvention',
            'sites' => 'sites du projet',
        ];
    }
}
