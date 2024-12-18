<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\FormulaireDeGouvernance;
use App\Models\Organisation;
use App\Models\PrincipeDeGouvernance;
use App\Models\Programme;
use App\Models\TypeDeGouvernance;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'intitule'          => 'required|max:255|unique:evaluations_de_gouvernance,intitule',
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
            //'debut'             => 'required|date|date_format:Y-m-d',
            //'fin'               => 'required|date|date_format:Y-m-d|after_or_equal:debut',
            'formulaires_de_gouvernance'     => ['required', 'array', 'min:2'],
            'formulaires_de_gouvernance.*'   => ['required', 'distinct', new HashValidatorRule(new FormulaireDeGouvernance())],
            'organisations'     => ['required', 'array', 'min:1'],
            'organisations.*'   => ['required', 'distinct', new HashValidatorRule(new Organisation())]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validatePrincipesCount($validator);
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

    private function validatePrincipesCount($validator)
    {
        if (count($this->input("formulaires_de_gouvernance")) < 2) {
            $validator->errors()->add(
                'formulaires_de_gouvernance',
                "Two form IDs are required in 'formulaires_de_gouvernance'."
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
                "Invalid 'formulaires_de_gouvernance' IDs provided."
            );
            return;
            //$fail("Invalid 'formulaires_de_gouvernance' IDs provided.");
        }

        if(!(($formulaire1->type === 'factuel' || $formulaire1->type === 'perception') && ($formulaire2->type === 'factuel' || $formulaire2->type === 'perception') && ($formulaire1->type !== $formulaire2->type))){
            throw ValidationException::withMessages(['formulaires_de_gouvernance.*' => "The selected form types must be either 'factuel' or 'perception' and cannot be the same."]);
        }
        else if($formulaire1->type === 'perception' && $formulaire2->type === 'factuel'){
            $factuelFormulaire = $formulaire2;
            $perceptionFormulaire = $formulaire1;
        }
        else if(($formulaire1->type === 'factuel' && $formulaire2->type === 'perception')){
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
                'formulaires_de_gouvernance' => "Mismatch in perception IDs between 'perception' and 'factuel' forms."
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
