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
            'organisationId'   => ['sometimes', Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation()), function ($attribute, $value, $fail) {
                if (request()->input($attribute)) {
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
            'pret' => ['required', 'integer', 'min:0', 'max:9999999999999', function () {
                if ($this->programmeId) {
                    $programme = Programme::findByKey($this->programmeId);
                    $budgetNational = $programme->budgetNational;
                    $totalPret = $programme->projets->sum('pret');

                    if (($totalPret + $this->pret) > $budgetNational) {
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
            'couleur.required' => 'La couleur du projet est obligatoire.',
            'debut.required' => 'La date de début du projet est obligatoire.',
            'debut.date' => 'La date de début doit être une date valide.',
            'debut.date_format' => 'La date de début doit être au format AAAA-MM-JJ.',
            'fin.required' => 'La date de fin du projet est obligatoire.',
            'fin.date' => 'La date de fin doit être une date valide.',
            'fin.date_format' => 'La date de fin doit être au format AAAA-MM-JJ.',
            'fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
            'pays.required' => 'Le pays du projet est obligatoire.',
            'pays.max' => 'Le nom du pays ne peut dépasser 255 caractères.',
            'bailleurId.required' => 'Le bailleur du projet est obligatoire.',
            'organisationId.required' => 'L’organisation porteuse est obligatoire.',
            'nombreEmploie.integer' => 'Le nombre d’emplois doit être un entier.',
            'image.file' => 'Le fichier doit être une image.',
            'image.mimes' => 'Les formats autorisés pour l’image sont : jpg, png, jpeg, webp, svg, ico.',
            'image.max' => 'La taille de l’image ne doit pas dépasser 2 Mo.',
            'fichier.array' => 'Les fichiers doivent être un tableau.',
            'fichier.*.file' => 'Chaque fichier doit être un fichier valide.',
            'fichier.*.mimes' => 'Formats autorisés : txt, doc, docx, xls, csv, xlsx, ppt, pdf, jpg, png, jpeg, mp3, wav, mp4, mov, avi, mkv.',
            'fichier.*.max' => 'Chaque fichier ne doit pas dépasser 2 Mo.',
            'budgetNational.required' => 'Le budget national du projet est obligatoire.',
            'budgetNational.integer' => 'Le budget national doit être un entier.',
            'budgetNational.min' => 'Le budget national doit être supérieur ou égal à 0.',
            'budgetNational.max' => 'Le budget national ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le montant de la subvention du projet est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.min' => 'Le montant de la subvention doit être supérieur ou égal à 0.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'sites.required' => 'Au moins un site doit être sélectionné.',
            'sites.array' => 'Les sites doivent être fournis sous forme de tableau.',
            'sites.min' => 'Au moins un site doit être sélectionné.',
            'sites.*.distinct' => 'Chaque site doit être unique.',
        ];
    }

    public function attributes()
    {
        return [
            'nom' => 'nom du projet',
            'statut' => 'statut du projet',
            'couleur' => 'couleur du projet',
            'debut' => 'date de début du projet',
            'fin' => 'date de fin du projet',
            'pays' => 'pays de realisation du projet',
            'organisationId' => 'organisation porteuse du projet',
            'bailleurId' => 'bailleur du projet',
            'nombreEmploie' => 'nombre d’emplois',
            'image' => 'image du projet',
            'fichier' => 'fichiers du projet',
            'budgetNational' => 'fond propre',
            'pret' => 'subvention',
            'sites' => 'sites du projet',
        ];
    }
}
