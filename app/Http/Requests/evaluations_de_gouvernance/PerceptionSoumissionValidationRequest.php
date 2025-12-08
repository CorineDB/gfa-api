<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\EvaluationDeGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\Organisation;
use App\Models\OptionDeReponse;
use App\Models\Programme;
use App\Models\QuestionDeGouvernance;
use App\Models\SourceDeVerification;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PerceptionSoumissionValidationRequest extends FormRequest
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
            'programmeId'               => ['required', new HashValidatorRule(new Programme())],
            'identifier_of_participant' => ['required'],
            'token'                     => ['bail', 'required', 'string', 'max:255', function ($attribute, $value, $fail) {
                $this->organisation = $this->evaluation_de_gouvernance->organisations(null,request()->input('token'))->first();
                if($this->organisation == null) $fail('Token inconnu.');

                $this->merge([
                    'organisationId' => $this->organisation->id, // Add or update the key-value pair
                ]);
            }],
            'formulaireDeGouvernanceId'   => ['bail', "required", new HashValidatorRule(new FormulaireDeGouvernance()), function ($attribute, $value, $fail) {
                    // Check if formulaireDeGouvernanceId exists within the related formulaires_de_gouvernance
                    $formulaire = $this->evaluation_de_gouvernance->formulaires_de_gouvernance()
                                        ->wherePivot('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))
                                        ->first();

                    if($formulaire == null) $fail('The selected formulaire de gouvernance ID is invalid or not associated with this evaluation.');
                    
                    $this->formulaireCache = $formulaire;

                    if(($soumission = $this->evaluation_de_gouvernance->soumissions->where('organisation', $this->organisation->id)->where('identifier_of_participant', request()->input('identifier_of_participant'))->where('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))->first()) && $soumission->statut === true){
                        $fail('La soumission a déjà été validée.');
                    }
                }
            ],
            'perception'                              => ['required', 'array', function($attribute, $value, $fail) {
                    if (count($value) < $this->getCountOfQuestionsOfAFormular()) {
                        $fail("Veuillez remplir tout le formulaire.");
                    }
                }
            ],
            'perception.categorieDeParticipant'       => ['required', 'in:membre_de_conseil_administration,employe_association,membre_association,partenaire'],
            'perception.sexe'                         => ['required', 'in:masculin,feminin'],
            'perception.age'                          => ['required', 'in:<35,>35'],

            'perception.response_data.*.questionId'      => ['required', 'distinct',
                new HashValidatorRule(new QuestionDeGouvernance()), 
                function($attribute, $value, $fail) {
                    $question = QuestionDeGouvernance::where("formulaireDeGouvernanceId", $this->formulaireCache->id)->where("type", "question_operationnelle")->findByKey($value)->exists();
                    if (!$question) {
                        // Fail validation if no response options are available
                        $fail("Cette question operationnelle n'existe pas.");
                    }

                    /*$this->indicateurCache = $indicateur;
                    
                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('organisationId', $this->organisationId)->where('indicateurDeGouvernanceId', $indicateur->id)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }*/
                }
            ],

            'perception.response_data.*.optionDeReponseId'   => ['required', new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given formulaire.');
                }
            }],
            
            'perception.commentaire'                => ['required', 'string'],
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


    /**
     * Returns the number of questions of the formulaire de gouvernance 
     * stored in the formulaireCache attribute
     * 
     * @return int
     */
    private function getCountOfQuestionsOfAFormular(){
        return 0;
        return $this->formulaireCache->questions_de_gouvernance->count();
    }
}
