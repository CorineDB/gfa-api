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
                        $fail("La date de début doit être dans l’année d’exercice sélectionnée ou après.");
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

            // -------------------------
            // INTITULÉ
            // -------------------------
            'intitule.required' => 'Veuillez saisir le nom de l’évaluation.',
            'intitule.string'   => 'Le nom de l’évaluation doit être composé de lettres et de chiffres.',
            'intitule.unique'   => 'Un autre outil utilise déjà ce nom, veuillez en choisir un autre.',

            // -------------------------
            // DESCRIPTION
            // -------------------------
            'description.max' => 'La description est trop longue (maximum 255 caractères).',

            // -------------------------
            // ANNÉE D’EXERCICE
            // -------------------------
            'annee_exercice.required' => 'Veuillez saisir l’année d’exercice.',
            'annee_exercice.integer'  => 'L’année d’exercice doit être un nombre.',

            // -------------------------
            // DATES
            // -------------------------
            'debut.required'     => 'Veuillez saisir la date de début.',
            'debut.date'         => 'La date de début n’est pas valide.',
            'debut.date_format'  => 'La date de début doit être au format AAAA-MM-JJ.',
            'debut.before'       => 'La date de début doit être avant la date de fin.',

            'fin.required'       => 'Veuillez saisir la date de fin.',
            'fin.date'           => 'La date de fin n’est pas valide.',
            'fin.date_format'    => 'La date de fin doit être au format AAAA-MM-JJ.',
            'fin.after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',


            // Formulaires de gouvernance
            'formulaires_de_gouvernance.required' => 'Veuillez sélectionner au moins un formulaire pour cette évaluation.',
            'formulaires_de_gouvernance.min'      => 'Vous devez soumettre au moins un formulaire.',
            'formulaires_de_gouvernance.max'      => 'Vous ne pouvez pas soumettre plus de deux formulaires.',
            'formulaires_de_gouvernance.array'    => 'Les formulaires doivent être envoyés sous forme de tableau.',

            'formulaires_de_gouvernance.factuel'    => 'Le formulaire factuel sélectionné est introuvable ou invalide.',
            'formulaires_de_gouvernance.perception' => 'Le formulaire de perception sélectionné est introuvable ou invalide.',

            'formulaires_de_gouvernance.factuel.distinct'     => 'Chaque formulaire factuel doit être unique.',
            'formulaires_de_gouvernance.perception.distinct'  => 'Chaque formulaire de perception doit être unique.',

            // Correspondance des principes entre formulaires
            'formulaires_de_gouvernance.mismatch_principes' => 'Pour que cette évaluation soit valide, les principes de gouvernance doivent être identiques dans les deux formulaires. Veuillez vérifier que les mêmes principes sont utilisés.',

            // Organisations participantes
            'organisations.required'       => 'Veuillez sélectionner au moins une organisation qui participera à l’évaluation.',
            'organisations.array'          => 'Les organisations doivent être envoyées sous forme de liste.',
            'organisations.min'            => 'Vous devez sélectionner au moins une organisation pour participer à l’évaluation.',
            'organisations.*.required'     => 'Chaque organisation sélectionnée doit être valide.',
            'organisations.*.distinct'     => 'Chaque organisation participante doit être unique.'
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

                    /* $validator->errors()->add(
                        'formulaires_de_gouvernance',
                        "Les principes de gouvernance du formulaire de perception doivent etre les memes dans le formulaire factuel."
                    ); */
                    // Step 3: Compare perception IDs across forms
                    $perceptionDiff = array_diff($perceptionPrincipesIds, $factuelPrincipesIds);
                    $factuelDiff    = array_diff($factuelPrincipesIds, $perceptionPrincipesIds);

                    if (!empty($perceptionDiff) || !empty($factuelDiff)) {
                        $message = "Les principes de gouvernance ne correspondent pas entre les formulaires.";

                        if (!empty($perceptionDiff)) {
                            $message .= " Principes manquants dans le formulaire factuel : " . implode(', ', $perceptionDiff) . ".";
                        }
                        if (!empty($factuelDiff)) {
                            $message .= " Principes manquants dans le formulaire de perception : " . implode(', ', $factuelDiff) . ".";
                        }

                        $validator->errors()->add('formulaires_de_gouvernance', $message);
                    }

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

    function normalize_string(string $str): string
    {
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
