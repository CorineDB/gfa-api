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
        $user = request()->user();

        // UG et Organisation avec permission peuvent modifier uniquement pour LEUR projet (projetable)
        if ($user->hasPermissionTo("modifier-une-activite") && ($user->hasRole("organisation") || $user->hasRole("unitee-de-gestion"))) {

            $activite = $this->route('activite');

            if (!is_object($activite)) {
                if (($activite = Activite::findByKey($activite))) {
                    throw ValidationException::withMessages(["activite" => "activite Inconnue"], 1);
                }
            }

            if ($activite) {
                $composante = $activite->composante;
                $projet = $composante ? $composante->projet : null;

                // Vérifier si le projet appartient à l'utilisateur (organisation ou UG)
                if ($projet) {
                    if ($projet->projetable_type === 'App\Models\Organisation' && $user->hasRole("organisation")) {
                        return $projet->projetable_id === $user->profilable->id;
                    }
                    if ($projet->projetable_type === 'App\Models\UniteeDeGestion' && $user->hasRole("unitee-de-gestion")) {
                        return $projet->projetable_id === $user->profilable->id;
                    }
                }
            }
        }

        return false;
    }

    public function prepareForValidation()
    {
        if (!is_object($this->activite)) {
            if (($activite = Activite::findByKey($this->activite))) {
                throw ValidationException::withMessages(["activite" => "Activite Inconnue"], 1);
            }

            $this->merge([
                "activite" => $activite->id
            ]);
        } else {
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
            'poids' => ['nullable', 'numeric', 'min:0'],
            'type' => 'sometimes|required|max:255',
            'composanteId' => ['required', new HashValidatorRule(new Composante())],
            'userId' => ['sometimes', 'required', new HashValidatorRule(new User())],
            'description' => 'string',
            //'structureResponsableId' => ['sometimes', 'required', new HashValidatorRule(new User())],
            //'structureAssocieId' => ['sometimes', 'required', new HashValidatorRule(new User())],

            'pret' => ['required', 'integer', 'min:0', 'max:9999999999999', function () {
                if ($this->composanteId) {
                    $composante = Composante::find($this->composanteId);
                    if ($composante) {
                        $pret = $composante->pret;
                        $totalpret = $composante->activites->where('id', '!=', $this->activite)->sum('pret');

                        if (($totalpret + $this->pret) > $pret) {
                            throw ValidationException::withMessages(["pret" => "Le total des budgets alloues aux activites de cet output ne peuvent pas dépasser le montant du budget alloue a l'output"], 1);
                        }
                    }
                }
            }],

            'budgetNational' => ['required', 'integer', 'min:0', 'max:9999999999999', function () {
                if ($this->composanteId) {
                    $composante = Composante::find($this->composanteId);
                    if ($composante) {
                        $budgetNational = $composante->budgetNational;
                        $totalBudgetNational = $composante->activites->where('id', '!=', $this->activite)->sum('budgetNational');

                        if (($totalBudgetNational + $this->budgetNational) > $budgetNational) {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des fonds propres des activites ne peuvent pas dépasser le montant du fond propre de l'output."], 1);
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
    /* public function messages()
    {
        return [
            'nom.required' => 'Le nom de l\'activité est obligatoire.',
            'statut.required' => 'Le statut de l\'activité est obligatoire.',
            'poids.required' => 'Le poids de l\'activité est obligatoire.',
            'type.required' => 'Le type de l\'activité est obligatoire.',
            'budgetNational.required' => 'Le fond propre de l\'activité est obligatoire.',
            'budgetNational.integer' => 'Le fond propre doit être un entier.',
            'budgetNational.max' => 'Le fond propre ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le montant de la subvention de l\'activité est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'tepPrevu.required' => 'Le tep prévu de l\'activité est obligatoire.',
            'userId.required' => 'Le responsable de l\'activité est obligatoire',
            'structureResponsableId.required' => 'La structure responsable de l\'activité est obligatoire',
            'structureAssocieId.required' => 'La structure associé de l\'activité est obligatoire',
            'composanteId.required' => 'La composante de l\'activité est obligatoire',
        ];
    } */

    public function messages()
    {
        return [
            'nom.required' => "Le nom de l'activité est obligatoire.",
            'nom.sometimes' => "Le nom de l'activité est obligatoire.",

            'poids.numeric' => "Le poids de l'activité doit être un nombre.",
            'poids.min' => "Le poids de l'activité ne peut pas être négatif.",

            'debut.required' => "La date de début de l'activité est obligatoire.",
            'debut.date' => "La date de début doit être une date valide.",
            'debut.date_format' => "La date de début doit être au format Y-m-d.",

            'fin.required' => "La date de fin de l'activité est obligatoire.",
            'fin.date' => "La date de fin doit être une date valide.",
            'fin.date_format' => "La date de fin doit être au format Y-m-d.",
            'fin.after_or_equal' => "La date de fin doit être postérieure ou égale à la date de début.",

            'type.required' => "Le type de l'activité est obligatoire.",
            'type.max' => "Le type ne peut pas dépasser 255 caractères.",

            'pret.required' => "Le montant de la subvention est obligatoire.",
            'pret.integer' => "Le montant de la subvention doit être un entier.",
            'pret.min' => "Le montant de la subvention doit être supérieur ou égal à 0.",
            'pret.max' => "Le montant de la subvention ne peut pas dépasser 9 999 999 999 999.",

            'budgetNational.required' => "Le fond propre de l'activité est obligatoire.",
            'budgetNational.integer' => "Le fond propre doit être un entier.",
            'budgetNational.min' => "Le fond propre doit être supérieur ou égal à 0.",

            'composanteId.required' => "La composante ou l'output parent est obligatoire.",
            'composanteId.*' => "La composante spécifiée est invalide.",
        ];
    }

    public function attributes()
    {
        return [
            'nom' => "nom de l'activité",
            'poids' => "poids de l'activité",
            'debut' => "date de début de l'activité",
            'fin' => "date de fin de l'activité",
            'type' => "type d'activité",
            'pret' => "montant de la subvention pour le financement de l'activité",
            'budgetNational' => "fond propre de financement de l'activité",
            'composanteId' => "output parent",
        ];
    }
}
