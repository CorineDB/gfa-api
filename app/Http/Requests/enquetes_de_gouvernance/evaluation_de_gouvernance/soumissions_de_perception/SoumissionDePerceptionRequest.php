<?php

namespace App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_de_perception;

use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\QuestionDePerceptionDeGouvernance;
use App\Models\EvaluationDeGouvernance;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class SoumissionDePerceptionRequest extends FormRequest
{
    protected $formulaireCache = null;
    protected $organisation = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !auth()->check() && $this->evaluation_de_gouvernance->statut == 0;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->evaluation_de_gouvernance))
        {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }

        return [
            'identifier_of_participant' => ['required'],
            'token'                     => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                $this->organisation = $this->evaluation_de_gouvernance->organisations(null,request()->input('token'))->first();
                if($this->organisation == null) $fail('Token inconnu.');

                $this->merge([
                    'organisationId' => $this->organisation->id, // Add or update the key-value pair
                ]);
            }],
            'formulaireDeGouvernanceId'   => [
                "required",
                new HashValidatorRule(new FormulaireDePerceptionDeGouvernance()),
                function ($attribute, $value, $fail) {

                    // Check if formulaireDeGouvernanceId exists within the related formulaire_factuel_de_gouvernance
                    $formulaire = $this->evaluation_de_gouvernance->formulaires_de_perception_de_gouvernance()
                        ->where('formulaireDePerceptionId', request()->input('formulaireDeGouvernanceId'))
                        ->first();

                    if ($formulaire == null) $fail('The selected formulaire de gouvernance ID is invalid or not associated with this evaluation.');

                    $this->formulaireCache = $formulaire;

                    if (($soumission = $this->evaluation_de_gouvernance->soumissionsDePerception->where('organisationId', $this->organisation->id)->where('identifier_of_participant', request()->input('identifier_of_participant'))->where('formulaireDePerceptionId', request()->input('formulaireDeGouvernanceId'))->first()) && $soumission->statut === true) {
                        $fail('La soumission a déjà été validée.');
                    }
                }
            ],

            'perception'                              => ['required', 'array'],
            'perception.categorieDeParticipant'       => ['nullable', 'in:membre_de_conseil_administration,employe_association,membre_association,partenaire'],
            'perception.sexe'                         => ['nullable', 'in:masculin,feminin'],
            'perception.age'                          => ['nullable', 'in:<35,>35'],

            'perception.response_data.*.questionId'      => [
                'sometimes',
                'distinct',
                new HashValidatorRule(new QuestionDePerceptionDeGouvernance()),
                function ($attribute, $value, $fail) {
                    $question = QuestionDePerceptionDeGouvernance::where("formulaireDePerceptionId", $this->formulaireCache->id)->findByKey($value)->exists();
                    if (!$question) {
                        // Fail validation if no response options are available
                        $fail("Cette question operationnelle n'existe pas.");
                    }
                }
            ],

            'perception.response_data.*.optionDeReponseId'   => ['sometimes', new HashValidatorRule(new OptionDeReponseGouvernance()), function ($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 *
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given formulaire.');
                }
            }],

            'perception.commentaire'                => ['nullable', 'string'],
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
}
