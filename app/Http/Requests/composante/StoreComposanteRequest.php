<?php

namespace App\Http\Requests\composante;

use App\Models\Composante;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreComposanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = request()->user();

        //dd($user->hasPermissionTo("creer-une-composante") && ($user->hasRole("organisation") || $user->hasRole("unitee-de-gestion")));

        dd($user->role);

        // UG et Organisation avec permission peuvent créer uniquement pour LEUR projet (projetable)
        if($user->hasPermissionTo("creer-une-composante") && ($user->hasRole("organisation") || $user->hasRole("unitee-de-gestion"))) {
            $projet = null;

            // Si c'est une composante directe du projet
            if($this->projetId) {
                $projet = Projet::find($this->projetId);
            }
            // Si c'est une sous-composante
            elseif($this->composanteId) {
                $composante = Composante::find($this->composanteId);
                $projet = $composante ? $composante->projet : null;
            }

            // Vérifier si le projet appartient à l'utilisateur (organisation ou UG)
            if($projet) {
                if($projet->projetable_type === 'App\Models\Organisation' && $user->hasRole("organisation")) {
                    return $projet->projetable_id === $user->organisation->id;
                }
                if($projet->projetable_type === 'App\Models\UniteDeGestion' && $user->hasRole("unitee-de-gestion")) {
                    return $projet->projetable_id === $user->uniteDeGestion->id;
                }
            }
        }

        return false;
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
            'poids' => ['nullable', 'numeric', 'min:0'],
            'projetId' => [ Rule::requiredIf(!$this->composanteId), 'bail', new HashValidatorRule(new Projet())],
            'composanteId' => [ Rule::requiredIf(!$this->projetId), 'bail', new HashValidatorRule(new Composante())],

            'pret' => ['required', 'integer', 'min:0', 'max:9999999999999', function(){
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

            'budgetNational' => ['required', 'integer', 'min:0', 'max:9999999999999', function(){
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
            'poids.required' => 'Le poids de la composante est obligatoire.',
            'budgetNational.required' => 'Le fond propre de la composante est obligatoire.',
            'budgetNational.integer' => 'Le fond propre doit être un entier.',
            'budgetNational.max' => 'Le fond propre ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le montant de la subvention de la composante est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'projetId.required' => 'Le projet est obligatoire.',
            'composanteId.required' => 'La composante de la sous composante est obligatoire.'
        ];
    }
}
