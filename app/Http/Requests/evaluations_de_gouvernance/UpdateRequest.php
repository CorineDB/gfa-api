<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\EvaluationDeGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\Organisation;
use App\Models\PrincipeDeGouvernance;
use App\Models\TypeDeGouvernance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-une-evaluation-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (is_string($this->evaluation_de_gouvernance)) {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }

        return [
            'intitule'               => ['sometimes', 'string', Rule::unique('evaluations_de_gouvernance', 'intitule')->ignore($this->evaluation_de_gouvernance)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            'annee_exercice'         => ['sometimes', 'integer'/* , Rule::unique('evaluations_de_gouvernance', 'annee_exercice')->ignore($this->evaluation_de_gouvernance)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at') */],

            'description'           => 'nullable|max:255',
            'debut'                 => 'sometimes|date|date_format:Y-m-d',
            'fin'                   => 'sometimes|date|date_format:Y-m-d|after_or_equal:debut',
            'formulaires_de_gouvernance'     => ['sometimes', 'array', 'min:1', 'max:2'],
            'formulaires_de_gouvernance.*'   => ['sometimes', 'distinct', new HashValidatorRule(new FormulaireDeGouvernance())],
            'organisations'         => ['sometimes', 'array', 'min:1'],
            'organisations.*'       => ['sometimes', 'distinct', new HashValidatorRule(new Organisation())]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validate($validator);
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

            // Custom messages for the 'principeDeGouvernanceId' field
            'principeDeGouvernanceId.required' => 'Le champ principe de gouvernance est obligatoire.',
        ];
    }

    public function validate($validator)
    {
        if ($this->evaluation_de_gouvernance->statut == 0) {

            if ($this->input("formulaires_de_gouvernance")) {
                $formulaires = $this->input("formulaires_de_gouvernance");

                if (count($formulaires) < 1) {
                    $validator->errors()->add(
                        'formulaires_de_gouvernance',
                        "Veuillez soumettre le formulaire factuel de gouvernance ou le formulaire de perception de gouvernance."
                    );
                    return;
                }

                if (count($formulaires) > 1) {

                    $formulaire1 = $formulaires[0];
                    $formulaire2 = $formulaires[1];

                    $formulaire1 = FormulaireDeGouvernance::find($formulaire1);
                    $formulaire2 = FormulaireDeGouvernance::find($formulaire2);

                    if (!$formulaire1 || !$formulaire2) {
                        $validator->errors()->add(
                            'formulaires_de_gouvernance',
                            "Formulaire de gouvernance inconnu"
                        );
                        return;
                    }

                    [$factuelFormulaire, $perceptionFormulaire] = $this->getForms($validator, $formulaire1, $formulaire2);

                    $evaluation_factuelFormulaire = $this->evaluation_de_gouvernance->formulaire_factuel_de_gouvernance();

                    $evaluation_perceptionFormulaire = $this->evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance();

                    if($evaluation_factuelFormulaire){
                        if($this->evaluation_de_gouvernance->soumissionsFactuel()->count()){
                            $factuelFormulaire = $evaluation_factuelFormulaire;
                        }
                    }

                    if($evaluation_perceptionFormulaire){
                        if($this->evaluation_de_gouvernance->soumissions()->where("type", 'perception')->count()){
                            $factuelFormulaire = $evaluation_perceptionFormulaire;
                        }
                    }

                    $this->formMatch($validator, $perceptionFormulaire, $factuelFormulaire);
                } else {
                    $formulaire1 = FormulaireDeGouvernance::find($formulaires[0]);

                    if (!$formulaire1) {
                        $validator->errors()->add(
                            'formulaires_de_gouvernance',
                            "Formulaire de gouvernance inconnu"
                        );
                        return;
                    }

                    if($formulaire1->type == 'factuel'){
                        $evaluation_formulaire = $this->evaluation_de_gouvernance->formulaire_de_perception_de_gouvernance();

                        $factuelFormulaire = $formulaire1;
                        $factuelFormulaire = $evaluation_formulaire;
                    }
                    else if($formulaire1->type == 'perception'){
                        $evaluation_formulaire = $this->evaluation_de_gouvernance->formulaire_factuel_de_gouvernance();

                        $factuelFormulaire = $evaluation_formulaire;
                        $perceptionFormulaire = $formulaire1;
                    }

                    if($evaluation_formulaire){
                        $this->formMatch($validator, $perceptionFormulaire, $factuelFormulaire);
                    }
                }
            }
        } else if ($this->evaluation_de_gouvernance->statut == -1) {
            $this->verifyPrincipesCount($validator);
        } else {
            // unset formulaires key
        }
    }

    private function verifyPrincipesCount($validator)
    {
        if ($this->input("formulaires_de_gouvernance")) {
            $formulaires = $this->input("formulaires_de_gouvernance");

            if (count($formulaires) < 1) {
                $validator->errors()->add(
                    'formulaires_de_gouvernance',
                    "Veuillez soumettre le formulaire factuel de gouvernance ou le formulaire de perception de gouvernance."
                );
                return;
            }

            if (count($formulaires) > 1) {

                $formulaire1 = $formulaires[0];
                $formulaire2 = $formulaires[1];

                $formulaire1 = FormulaireDeGouvernance::find($formulaire1);
                $formulaire2 = FormulaireDeGouvernance::find($formulaire2);

                if (!$formulaire1 || !$formulaire2) {
                    $validator->errors()->add(
                        'formulaires_de_gouvernance',
                        "Formulaire de gouvernance inconnu"
                    );
                    return;
                }

                [$factuelFormulaire, $perceptionFormulaire] = $this->getForms($validator, $formulaire1, $formulaire2);

                $this->formMatch($validator, $perceptionFormulaire, $factuelFormulaire);
            } else {
                $formulaire1 = $formulaires[0];
                $formulaire1 = FormulaireDeGouvernance::find($formulaire1);

                if (!$formulaire1) {
                    $validator->errors()->add(
                        'formulaires_de_gouvernance',
                        "Formulaire de gouvernance inconnu"
                    );
                    return;
                }
            }
        }
    }

    private function getForms($validator, $formulaire1, $formulaire2){

        if (!(($formulaire1->type === 'factuel' || $formulaire1->type === 'perception') && ($formulaire2->type === 'factuel' || $formulaire2->type === 'perception') && ($formulaire1->type !== $formulaire2->type))) {

            $validator->errors()->add(
                'formulaires_de_gouvernance.*',
                "Les formulaires doivent etre factuel et de perception."
            );
        } else if ($formulaire1->type === 'perception' && $formulaire2->type === 'factuel') {
            $factuelFormulaire = $formulaire2;
            $perceptionFormulaire = $formulaire1;
        } else if (($formulaire1->type === 'factuel' && $formulaire2->type === 'perception')) {
            $factuelFormulaire = $formulaire1;
            $perceptionFormulaire = $formulaire2;
        }

        return [$factuelFormulaire, $perceptionFormulaire];
    }


    private function formMatch($validator, $perceptionFormulaire, $factuelFormulaire){

        // Step 1: Retrieve Perception IDs from the 'perception' form
        $perceptionIds = DB::table('categories_de_gouvernance')
            ->where('formulaireDeGouvernanceId', $perceptionFormulaire->id)
            ->whereNull('categorieDeGouvernanceId')
            ->pluck('categorieable_id')
            ->toArray();

        // Step 2: Retrieve unique Perception IDs from the 'factuel' form
        $form2TypesWithPerceptionIds = DB::table('categories_de_gouvernance as types')
            ->join('categories_de_gouvernance as perceptions', 'types.id', '=', 'perceptions.categorieDeGouvernanceId')
            ->where('types.formulaireDeGouvernanceId', $factuelFormulaire->id)
            ->whereNull('types.categorieDeGouvernanceId')
            ->where('types.categorieable_type', get_class(new TypeDeGouvernance()))
            ->where('perceptions.categorieable_type', get_class(new PrincipeDeGouvernance()))
            ->select('perceptions.categorieable_id as perception_id')
            ->distinct() // Ignore duplicates by selecting only unique perception IDs
            ->pluck('perception_id')
            ->toArray();

        // Step 3: Compare perception IDs across forms
        if (array_diff($perceptionIds, $form2TypesWithPerceptionIds) || array_diff($form2TypesWithPerceptionIds, $perceptionIds)) {

            $validator->errors()->add(
                'formulaires_de_gouvernance',
                "Les principes de gouvernance du formulaire de perception doivent etre les memes dans le formulaire factuel."
            );
        }
    }

    private function validatePrincipesCount($validator)
    {
        if ($this->input("formulaires_de_gouvernance")) {
            if (count($this->input("formulaires_de_gouvernance")) < 1) {
                $validator->errors()->add(
                    'formulaires_de_gouvernance',
                    "Veuillez soumettre le formulaire factuel de gouvernance ou le formulaire de perception de gouvernance."
                );
                return;
            }

            [$formulaireDeGouvernanceId, $perceptionFormulaire] = $this->input("formulaires_de_gouvernance");


            $formulaire1 = FormulaireDeGouvernance::find($formulaireDeGouvernanceId);
            $formulaire2 = FormulaireDeGouvernance::find($perceptionFormulaire);

            //dd($formulaire1->categories_de_gouvernance->first()->sousCategoriesDeGouvernance);

            if (!$formulaire1 || !$formulaire2) {
                $validator->errors()->add(
                    'formulaires_de_gouvernance',
                    "Formulaire de gouvernance inconnu"
                );
                return;
                //$fail("Invalid 'formulaires_de_gouvernance' IDs provided.");
            }

            if (!(($formulaire1->type === 'factuel' || $formulaire1->type === 'perception') && ($formulaire2->type === 'factuel' || $formulaire2->type === 'perception') && ($formulaire1->type !== $formulaire2->type))) {
                throw ValidationException::withMessages(['formulaires_de_gouvernance.*' => "Les formulaires doivent etre factuel et de perception."]);
            } else if ($formulaire1->type === 'perception' && $formulaire2->type === 'factuel') {
                $factuelFormulaire = $formulaire2;
                $perceptionFormulaire = $formulaire1;
            } else if (($formulaire1->type === 'factuel' && $formulaire2->type === 'perception')) {
                $factuelFormulaire = $formulaire1;
                $perceptionFormulaire = $formulaire2;
            }

            // Step 1: Retrieve Perception IDs from the 'perception' form
            $perceptionIds = DB::table('categories_de_gouvernance')
                ->where('formulaireDeGouvernanceId', $perceptionFormulaire->id)
                ->whereNull('categorieDeGouvernanceId')
                ->pluck('categorieable_id')
                ->toArray();

            // Step 2: Retrieve unique Perception IDs from the 'factuel' form
            $form2TypesWithPerceptionIds = DB::table('categories_de_gouvernance as types')
                ->join('categories_de_gouvernance as perceptions', 'types.id', '=', 'perceptions.categorieDeGouvernanceId')
                ->where('types.formulaireDeGouvernanceId', $factuelFormulaire->id)
                ->whereNull('types.categorieDeGouvernanceId')
                ->where('types.categorieable_type', get_class(new TypeDeGouvernance))
                ->where('perceptions.categorieable_type', get_class(new PrincipeDeGouvernance))
                ->select('perceptions.categorieable_id as perception_id')
                ->distinct() // Ignore duplicates by selecting only unique perception IDs
                ->pluck('perception_id')
                ->toArray();

            // Step 3: Compare perception IDs across forms
            if (array_diff($perceptionIds, $form2TypesWithPerceptionIds) || array_diff($form2TypesWithPerceptionIds, $perceptionIds)) {
                throw ValidationException::withMessages([
                    'formulaires_de_gouvernance' => "Les principes de gouvernance du formulaire de perception doivent etre les memes dans le formulaire factuel."
                ]);
            }

            // Get perception IDs from Form 1 (perception form)
            // Step 1: Retrieve Perception IDs from the 'perception' form
            /* $perceptionIds = DB::table('categories_de_gouvernance')
                ->where('formulaireDeGouvernanceId', $perceptionFormulaire->id)
                ->where('categorieDeGouvernanceId', null)
                ->pluck('categorieable_id')
                ->toArray(); */

            // Get each 'type de gouvernance' in Form 2 and its perception IDs (subcategories)
            /* $form2TypesWithPerceptionIds = DB::table('categories_de_gouvernance as types')
                ->join('categories_de_gouvernance as perceptions', 'types.id', '=', 'perceptions.categorieDeGouvernanceId')
                ->where('types.categorieable_type', get_class(new TypeDeGouvernance))
                ->where('types.categorieDeGouvernanceId', NULL)
                ->where('types.formulaireDeGouvernanceId', $factuelFormulaire->id)
                ->where('perceptions.formulaireDeGouvernanceId', $factuelFormulaire->id)
                ->where('perceptions.categorieable_type', get_class(new PrincipeDeGouvernance))
                ->select('types.id as type_id', 'perceptions.categorieable_id as perception_id')
                ->get()
                ->groupBy('type_id'); */

            // Validate that each type de gouvernance's perception IDs in Form 2 match perception IDs in Form 1
            /* foreach ($form2TypesWithPerceptionIds as $typeId => $perceptions) {

                $typePerceptionIds = $perceptions->pluck('perception_id')->toArray();

                // Check if Form 1 perception IDs match the perception IDs under each type de gouvernance in Form 2
                if (array_diff($perceptionIds, $typePerceptionIds) || array_diff($typePerceptionIds, $perceptionIds)) {
                    throw ValidationException::withMessages([
                        'formulaires_de_gouvernance' => "Perceptions in Form 1 do not match perceptions in each type de gouvernance in Form 2."
                    ]);
                }
            } */
        }
    }
}
