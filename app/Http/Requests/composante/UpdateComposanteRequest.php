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
    protected $composant = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = request()->user();

        // UG et Organisation avec permission peuvent modifier uniquement pour LEUR projet (projetable)
        if ($user->hasPermissionTo("modifier-une-composante") && ($user->hasRole("organisation") || $user->hasRole("unitee-de-gestion"))) {

            $composante = $this->route('composante');

            if (!is_object($composante)) {
                if (($composante = Composante::findByKey($composante))) {
                    throw ValidationException::withMessages(["composante" => "Composante Inconnue"], 1);
                }
            }

            if ($composante) {
                $projet = $composante->projet;

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
        if (!is_object($this->composante)) {
            if (($composante = Composante::findByKey($this->composante))) {
                throw ValidationException::withMessages(["composante" => "Composante Inconnue"], 1);
            }

            $this->merge([
                "composante" => $composante->id
            ]);
        } else {
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
            'poids' => ['nullable', 'numeric', 'min:0'],
            'projetId' => ['sometimes',  Rule::requiredIf(!$this->composanteId), new HashValidatorRule(new Projet())],
            'composanteId' => ['sometimes',  Rule::requiredIf(!$this->projetId), new HashValidatorRule(new Composante())],

            'pret' => ['sometimes', 'integer', 'min:0', 'max:9999999999999', function () {
                if ($this->projetId) {
                    $projet = Projet::find($this->projetId);
                    if ($projet) {
                        $pret = $projet->pret;
                        $totalpret = $projet->composantes->where('id', '!=', $this->composante)->sum('pret');

                        if (($totalpret + $this->pret) > $pret) {
                            throw ValidationException::withMessages(["pret" => "Le total des budgets alloues aux outcomes de ce projet ne peuvent pas dépasser le montant du budget alloue au projet"], 1);
                        }
                    }
                } elseif ($this->composanteId) {
                    $composante = Composante::find($this->composanteId);
                    if ($composante) {
                        $pret = $composante->pret;
                        $totalpret = $composante->sousComposantes->where('id', '!=', $this->composante)->sum('pret');

                        if (($totalpret + $this->pret) > $pret) {
                            throw ValidationException::withMessages(["pret" => "Le total des budgets alloues aux outputs de cet outcome ne peuvent pas dépasser le montant du budget alloue a l'outcome"], 1);
                        }
                    }
                }


                if ($this->composante) {
                    $c = Composante::find($this->composante);
                    if ($c) {
                        // Ancien code - Logique inversée
                        // $pret = $c->pret;
                        // $totalpretA = $c->activites->sum('pret');
                        // if($totalpretA > $this->pret)
                        // {
                        //     throw ValidationException::withMessages(["pret" => "Le montant du budget alloue a l'output/outcome ne peuvent pas dépasser le total des budgets alloues aux activites de l'output/outcone."], 1);
                        // }

                        // Nouveau code - Logique corrigée
                        $totalpretSousComposantes = $c->sousComposantes->sum('pret');
                        $totalpretActivites = $c->activites->sum('pret');

                        if ($this->pret < $totalpretSousComposantes) {
                            throw ValidationException::withMessages(["pret" => "Le montant du budget alloue a l'outcome ne peut pas etre inferieur au total des budgets alloues aux outputs de cet outcome."], 1);
                        }

                        if ($this->pret < $totalpretActivites) {
                            throw ValidationException::withMessages(["pret" => "Le montant du budget alloue a l'output ne peut pas etre inferieur au total des budgets alloues aux activites de cet output."], 1);
                        }
                    }
                }
            }],

            'budgetNational' => ['sometimes', 'integer', 'min:0', 'max:9999999999999', function () {
                if ($this->projetId) {
                    $projet = Projet::find($this->projetId);
                    if ($projet) {
                        $budgetNational = $projet->budgetNational;
                        $totalBudgetNational = $projet->composantes->where('id', '!=', $this->composante)->sum('budgetNational');

                        if (($totalBudgetNational + $this->budgetNational) > $budgetNational) {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des fonds propres aux outcomes de ce projet ne peut pas dépasser le montant du fond propre du projet"], 1);
                        }
                    }
                } elseif ($this->composanteId) {
                    $composante = Composante::find($this->composanteId);
                    if ($composante) {
                        $budgetNational = $composante->budgetNational;
                        $totalBudgetNational = $composante->sousComposantes->where('id', '!=', $this->composante)->sum('budgetNational');

                        if (($totalBudgetNational + $this->budgetNational) > $budgetNational) {
                            throw ValidationException::withMessages(["budgetNational" => "Le total des fonds propres des outputs de cet outcome ne peuvent pas dépasser le montant du fond propre de l'outcome"], 1);
                        }
                    }
                }


                if ($this->composante) {
                    $c = Composante::find($this->composante);
                    if ($c) {
                        // Ancien code - Logique inversée
                        // $budgetNational = $c->budgetNational;
                        // $totalBudgetNational = $c->activites->sum('budgetNational');
                        // if($totalBudgetNational > $this->budgetNational)
                        // {
                        //     throw ValidationException::withMessages(["budgetNational" => "Le montant du budget alloue a l'output/outcome ne peuvent pas dépasser le total des budgets alloues aux activites de l'output/outcone."], 1);
                        // }

                        // Nouveau code - Logique corrigée
                        $totalBudgetNationalSousComposantes = $c->sousComposantes->sum('budgetNational');
                        $totalBudgetNationalActivites = $c->activites->sum('budgetNational');

                        if ($this->budgetNational < $totalBudgetNationalSousComposantes) {
                            throw ValidationException::withMessages(["budgetNational" => "Le montant du fond propre alloue a l'outcome ne peut pas etre inferieur au total des fonds propres alloues aux outputs de cet outcome."], 1);
                        }

                        if ($this->budgetNational < $totalBudgetNationalActivites) {
                            throw ValidationException::withMessages(["budgetNational" => "Le montant du fond propre alloue a l'output ne peut pas etre inferieur au total des fonds propres alloues aux activites de cet output."], 1);
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
            'nom.required' => 'Le nom de la composante est obligatoire.',
            'statut.required' => 'Le statut de la composante est obligatoire.',
            'poids.required' => 'Le poids de la composante est obligatoire.',
            'budgetNational.required' => 'Le fond propre de la composante est obligatoire.',
            'budgetNational.integer' => 'Le fond propre doit être un entier.',
            'budgetNational.max' => 'Le fond propre ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le montant de la subvention de la composante est obligatoire.',
            'pret.integer' => 'Le montant de la subvention doit être un entier.',
            'pret.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'tepPrevu.required' => 'Le tep prévu de la composante est obligatoire.',
            'projetId.required' => 'Le projet de la composante est obligatoire.',
            'projetId.exists' => 'Ce projet n\'existe pas',
            'composanteId.required' => 'La sous composante  est obligatoire.',
        ];
    } */



    public function messages()
    {
        return [
            'nom.required' => "L' :attribute est obligatoire.",
            'poids.required' => 'Le :attribute est obligatoire.',
            'budgetNational.required' => 'Le :attribute est obligatoire.',
            'budgetNational.integer' => 'Le :attribute doit être un entier.',
            'budgetNational.max' => 'Le :attribute ne peut pas dépasser 9 999 999 999 999 CFA.',
            'pret.required' => 'Le :attribute est obligatoire.',
            'pret.integer' => 'Le :attribute doit être un entier.',
            'pret.max' => 'Le :attribute ne peut pas dépasser 9 999 999 999 999 CFA.',
            'projetId.required' => 'Le :attribute est obligatoire.',
            'composanteId.required' => "L' :attribute est obligatoire."
        ];
    }

    public function attributes()
    {
        return [
            'nom' => $this->composanteId ? 'nom de l\'output' : 'nom de l\'outcome',
            'poids' => $this->composanteId ? 'poids de l\'output' : 'poids de l\'outcome',
            'budgetNational' => $this->composanteId ? 'fond propre de l\'output' : 'fond propre de l\'outcome',
            'pret' => $this->composanteId ? 'montant de la subvention de l\'output' : 'montant de la subvention de l\'outcome',
            'projetId' => 'projet',
            'composanteId' => $this->composant ? ($this->composant->composanteId ? 'output parent' : 'outcome parent') : 'outcome parent',
        ];
    }
}
