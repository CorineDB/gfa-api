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
use Illuminate\Validation\Rule;
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
	dd($this->input("formulaires_de_gouvernance"));
        if ($this->input("formulaires_de_gouvernance")) {
            $formulaires = $this->input("formulaires_de_gouvernance");

            if (count($formulaires) <= 0) {
                $validator->errors()->add(
                    'formulaires_de_gouvernance',
                    "Veuillez soumettre le formulaire factuel de gouvernance ou le formulaire de perception de gouvernance."
                );
                return;
            }

            //[$formulaireDeGouvernanceId, $perceptionFormulaire] = $this->input("formulaires_de_gouvernance");

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
                    //$fail("Invalid 'formulaires_de_gouvernance' IDs provided.");
                }

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
		dd($perceptionIds, $form2TypesWithPerceptionIds);
                // Step 3: Compare perception IDs across forms
                if (array_diff($perceptionIds, $form2TypesWithPerceptionIds) || array_diff($form2TypesWithPerceptionIds, $perceptionIds)) {

                    $validator->errors()->add(
                        'formulaires_de_gouvernance',
                        "Les principes de gouvernance du formulaire de perception doivent etre les memes dans le formulaire factuel."
                    );
                }
            } else {
                $formulaire1 = $formulaires[0];
                $formulaire1 = FormulaireDeGouvernance::find($formulaire1);

                if (!$formulaire1) {
                    $validator->errors()->add(
                        'formulaires_de_gouvernance.0',
                        "Formulaire de gouvernance inconnu"
                    );
                    return;
                    //$fail("Invalid 'formulaires_de_gouvernance' IDs provided.");
                }
            }
        }
    }
}
