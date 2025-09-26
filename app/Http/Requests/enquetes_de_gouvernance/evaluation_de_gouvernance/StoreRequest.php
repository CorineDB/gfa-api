<?php

namespace App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance;

use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel;
use App\Models\Organisation;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-une-evaluation-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'intitule'               => ['required', 'string', Rule::unique('evaluations_de_gouvernance', 'intitule')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'annee_exercice'    => 'required|integer',
            'description'       => 'nullable|max:255',
            'debut'             => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before:fin',
                function ($attribute, $value, $fail) {
                    $anneeExercice = $this->input('annee_exercice');
                    if (date('Y', strtotime($value)) < $anneeExercice) {
                        $fail("The $attribute must be equal to or later than the start of annee_exercice.");
                    }
                }
            ],
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut',
            'formulaires_de_gouvernance'     => ['required', 'array', 'min:1', 'max:2'],
            'formulaires_de_gouvernance.factuel'   => ['sometimes', 'distinct', new HashValidatorRule(new FormulaireFactuelDeGouvernance())],

            'formulaires_de_gouvernance.perception'   => ['sometimes', 'distinct', new HashValidatorRule(new FormulaireDePerceptionDeGouvernance())],

            'organisations'     => ['required', 'array', 'min:1'],
            'organisations.*'   => ['required', 'distinct', new HashValidatorRule(new Organisation())]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->checkPrincipesMatch($validator);
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom messages for the 'nom' field
            'nom.required'      => 'Le champ nom est obligatoire.',
            'nom.max'           => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique'        => 'Ce nom est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'programmeId' field
            'programmeId.required' => 'Le champ programme est obligatoire.',

        ];
    }

    private function checkPrincipesMatch($validator)
    {
        $formulaires = $this->input("formulaires_de_gouvernance");
        if ($formulaires) {

            if (count($formulaires) > 1) {

                $formulaireFactuel = $formulaires['factuel'];
                $formulaireFactuel = FormulaireFactuelDeGouvernance::find($formulaireFactuel);

                if (!$formulaireFactuel) {
                    $validator->errors()->add(
                        'formulaires_de_gouvernance.factuel',
                        "Formulaire de gouvernance factuel inconnu"
                    );
                    return;
                }

                $formulaireDePerception = $formulaires['perception'];
                $formulaireDePerception = FormulaireDePerceptionDeGouvernance::find($formulaireDePerception);

                if (!$formulaireDePerception) {
                    $validator->errors()->add(
                        'formulaires_de_gouvernance.perception',
                        "Formulaire de gouvernance de perception inconnu"
                    );
                    return;
                }

		/*
                // Step 1: Retrieve Principe IDs from the 'perception' form
                $perceptionPrincipesIds = DB::table('categories_de_perception_de_gouvernance')
                    ->where('formulaireDePerceptionId', $formulaireDePerception->id)
                    ->whereNull('categorieDePerceptionDeGouvernanceId')
                    ->pluck('categorieable_id')
                    ->toArray();

                // Step 2: Retrieve unique Principe IDs from the 'factuel' form
                $factuelPrincipesIds = DB::table('categories_factuel_de_gouvernance as types')
                    ->join('categories_factuel_de_gouvernance as principes', 'types.id', '=', 'principes.categorieFactuelDeGouvernanceId')
                    ->where('types.formulaireFactuelId', $formulaireFactuel->id)
                    ->whereNull('types.categorieFactuelDeGouvernanceId')
                    ->where('principes.formulaireFactuelId', $formulaireFactuel->id)
                    ->whereNotNull('principes.categorieFactuelDeGouvernanceId')
                    ->where('types.categorieable_type', get_class(new TypeDeGouvernanceFactuel()))
                    ->where('principes.categorieable_type', get_class(new PrincipeDeGouvernanceFactuel()))
                    ->select('principes.categorieable_id as principe_id')
                    ->distinct() // Ignore duplicates by selecting only unique perception IDs
                    ->pluck('principe_id')
                    ->toArray();
		*/

		$perceptionPrincipesIds = DB::table('categories_de_perception_de_gouvernance as principes')
                                          ->join('principes_de_gouvernance_de_perception as pgp', 'pgp.id', '=', 'principes.categorieable_id')
    					  ->where('formulaireDePerceptionId', $formulaireDePerception->id)
    					  ->whereNull('categorieDePerceptionDeGouvernanceId')
    					  ->select('pgp.nom as principe_nom')
   					  ->pluck('principe_nom')
    					  ->map(fn($v) => $this->normalize_string($v))
    			                  ->toArray();


		$factuelPrincipesIds = DB::table('categories_factuel_de_gouvernance as types')
                        ->join('categories_factuel_de_gouvernance as principes', 'types.id', '=', 'principes.categorieFactuelDeGouvernanceId')
                        ->join('principes_de_gouvernance_factuel as pgf', 'pgf.id', '=', 'principes.categorieable_id')
			->where('types.formulaireFactuelId', $formulaireFactuel->id)
    			->whereNull('types.categorieFactuelDeGouvernanceId')
    			->where('principes.formulaireFactuelId', $formulaireFactuel->id)
    			->whereNotNull('principes.categorieFactuelDeGouvernanceId')
    			->where('types.categorieable_type', get_class(new TypeDeGouvernanceFactuel()))
    			->where('principes.categorieable_type', get_class(new PrincipeDeGouvernanceFactuel()))
    			->select('pgf.nom as principe_nom')
    			->distinct()
    			->pluck('principe_nom')
    			->map(fn($v) => $this->normalize_string($v))
    			->toArray();

                //dd($perceptionPrincipesIds, $factuelPrincipesIds);

                // Step 3: Compare perception IDs across forms
                //if (array_diff($perceptionPrincipesIds, $factuelPrincipesIds) || array_diff($factuelPrincipesIds, $perceptionPrincipesIds)) {
                if (!empty(array_diff($perceptionPrincipesIds, $factuelPrincipesIds)) || !empty(array_diff($factuelPrincipesIds, $perceptionPrincipesIds))) {

                    $validator->errors()->add(
                        'formulaires_de_gouvernance',
                        "Les principes de gouvernance du formulaire de perception doivent etre les memes dans le formulaire factuel."
                    );
                }
            } else {

                if (isset($formulaires['factuel'])) {
                    $formulaire = $formulaires['factuel'];
                    $formulaire = FormulaireFactuelDeGouvernance::find($formulaire);

                    if (!$formulaire) {
                        $validator->errors()->add(
                            'formulaires_de_gouvernance.factuel',
                            "Formulaire de gouvernance factuel inconnu"
                        );
                        return;
                    }
                } else if (isset($formulaires['perception'])) {
                    $formulaire = $formulaires['perception'];
                    $formulaire = FormulaireDePerceptionDeGouvernance::find($formulaire);

                    if (!$formulaire) {
                        $validator->errors()->add(
                            'formulaires_de_gouvernance.perception',
                            "Formulaire de gouvernance de perception inconnu"
                        );
                        return;
                    }
                }
            }
        } else {
            $validator->errors()->add(
                'formulaires_de_gouvernance',
                "Veuillez soumettre au moins le formulaire d'un outil"
            );
        }
    }

  function normalize_string(string $str): string {
    // 1. Supprime les espaces en début/fin
    $str = trim($str);
    // 2. Remplace les multiples espaces par un seul
    $str = preg_replace('/\s+/', ' ', $str);
    // 3. Met en minuscules
    $str = mb_strtolower($str, 'UTF-8');
    // 4. Normalise les accents
    $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    return $str;
}
}
