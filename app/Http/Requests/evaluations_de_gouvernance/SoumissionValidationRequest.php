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

class SoumissionValidationRequest extends FormRequest
{
    protected $formulaireCache = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(is_string($this->evaluation_de_gouvernance))
        {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }
        //return request()->user()->hasRole("unitee-de-gestion") && $this->evaluation_de_gouvernance->statut;

        return (request()->user()->hasRole("unitee-de-gestion") && request()->user()->hasRole("organisation")) && $this->evaluation_de_gouvernance->statut == 0;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'programmeId'   => [Rule::requiredIf(!auth()->check()), new HashValidatorRule(new Programme())],
            'organisationId'   => [Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],
            'formulaireDeGouvernanceId'   => ["required", new HashValidatorRule(new FormulaireDeGouvernance()), function ($attribute, $value, $fail) {
                    // Check if formulaireDeGouvernanceId exists within the related formulaires_de_gouvernance
                    $formulaire = $this->evaluation_de_gouvernance->formulaires_de_gouvernance()
                                        ->wherePivot('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))
                                        ->first();

                    if($formulaire == null) $fail('The selected formulaire de gouvernance ID is invalid or not associated with this evaluation.');
                    
                    $this->formulaireCache = $formulaire;

                    if(($soumission = $this->evaluation_de_gouvernance->soumissions->where('organisationId', request()->input('organisationId') ?? auth()->user()->profileable->id)->where('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))->first()) && $soumission->statut === true){
                        $fail('La soumission a déjà été validée.');
                    }
                }
            ],

            'factuel'                                         => ['required', 'array'],
            
            'factuel.comite_members'                                        => ['required', 'array', 'min:1'],
            'factuel.comite_members.*.nom'                                  => ['required', 'string'],
            'factuel.comite_members.*.prenom'                               => ['required', 'string'],
            'factuel.comite_members.*.contact'                              => ['required', 'distinct', 'numeric','digits_between:8,24'],
            'factuel.response_data'                                 => [Rule::requiredIf(!request()->input('perception')), 'array', function($attribute, $value, $fail) {
                    if (count($value) < $this->getCountOfQuestionsOfAFormular()) {
                        $fail("Veuillez remplir tout le formulaire.");
                    }
                }
            ],            
            'factuel.response_data.*.questionId'      => [Rule::requiredIf(!request()->input('perception')), 'distinct', 
                new HashValidatorRule(new QuestionDeGouvernance()),
                function($attribute, $value, $fail) {
                    
                    if($this->formulaireCache){
                        $question = QuestionDeGouvernance::where("formulaireDeGouvernanceId", $this->formulaireCache->id)->where("type", "indicateur")->findByKey($value)->exists();
                        if (!$question) {
                            // Fail validation if no response options are available
                            $fail("Cet Indicateur n'existe pas.");
                        }
                    }

                    /*$this->indicateurCache = $indicateur;
                    
                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('organisationId', $this->organisationId)->where('indicateurDeGouvernanceId', $indicateur->id)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }*/
                }
            ],
            'factuel.response_data.*.optionDeReponseId'   => [Rule::requiredIf(!request()->input('perception')), new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if($this->formulaireCache){
                    if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                        $fail('The selected option is invalid for the given formulaire.');
                    }
                }
            }],
            'factuel.response_data.*.sourceDeVerificationId'        => [Rule::requiredIf(!request()->input('factuel.response_data.*.sourceDeVerification')), new HashValidatorRule(new SourceDeVerification())], 
            'factuel.response_data.*.sourceDeVerification'          => [ Rule::requiredIf(!request()->input('factuel.response_data.*.sourceDeVerificationId'))],
            'factuel.response_data.*.preuves'                       => ['required', "array", "min:1"],
            'factuel.response_data.*.preuves.*'                     => ["file", "mimes:doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv", "mimetypes:application/pdf,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,audio/mpeg,audio/wav,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska", "max:20480"],

            'perception'                              => [Rule::requiredIf(!request()->input('factuel')), 'array', function($attribute, $value, $fail) {
                    if (count($value) < $this->getCountOfQuestionsOfAFormular()) {
                        $fail("Veuillez remplir tout le formulaire.");
                    }
                }
            ],
            'perception.categorieDeParticipant'       => [Rule::requiredIf(!request()->input('factuel')), 'in:membre_de_conseil_administration,employe_association,membre_association,partenaire'],
            'perception.sexe'                         => [Rule::requiredIf(!request()->input('factuel')), 'in:masculin,feminin'],
            'perception.age'                          => [Rule::requiredIf(!request()->input('factuel')), 'in:<35,>35'],

            'perception.response_data.*.questionId'      => [Rule::requiredIf(!request()->input('factuel')), 'distinct',
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

            'perception.response_data.*.optionDeReponseId'   => [Rule::requiredIf(!request()->input('factuel')), new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given formulaire.');
                }
            }],
            
            'perception.commentaire'                => [Rule::requiredIf(!request()->input('factuel')), 'string'],
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
        return $this->formulaireCache->questions_de_gouvernance->count();
    }
}
