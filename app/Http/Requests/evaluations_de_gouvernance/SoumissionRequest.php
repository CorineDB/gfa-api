<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\EvaluationDeGouvernance;
use App\Models\Formulaire;
use App\Models\FormulaireDeGouvernance;
use App\Models\Organisation;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\QuestionDeGouvernance;
use App\Models\SourceDeVerification;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SoumissionRequest extends FormRequest
{
    protected $indicateurCache = null;
    protected $formulaireCache = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasRole("unitee-de-gestion") && $this->evaluation_de_gouvernance->statut;
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
            'organisationId'   => [Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],
            'formulaireDeGouvernanceId'   => ["required", new HashValidatorRule(new FormulaireDeGouvernance()), function ($attribute, $value, $fail) {
                
                // Check if formulaireDeGouvernanceId exists within the related formulaires_de_gouvernance
                $formulaire = $this->evaluation_de_gouvernance->formulaires_de_gouvernance()
                ->where('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))
                ->first();
                
                if(!$formulaire) $fail('The selected formulaire de gouvernance ID is invalid or not associated with this evaluation.');

                $this->formulaireCache = $formulaire;
            }],


            'response_data'        => ['required', 'array', 'min:1'],
            'response_data.factuel'      => [Rule::requiredIf(!request()->input('response_data.perception')), 'array', 'min:1'],

            'response_data.factuel.*.indicateurDeGouvernanceId'      => [Rule::requiredIf(!request()->input('response_data.perception')), 'distinct', 
                new HashValidatorRule(new QuestionDeGouvernance()), 
                function($attribute, $value, $fail) {
                    $question = QuestionDeGouvernance::where("formulaireDeGouvernanceId", $this->formulaireCache->id)->where("type", "indicateur")->findByKey($value)->exists();
                    if (!$question) {
                        // Fail validation if no response options are available
                        $fail("Cet Indicateur n'existe pas.");
                    }

                    /*$this->indicateurCache = $indicateur;
                    
                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('organisationId', $this->organisationId)->where('indicateurDeGouvernanceId', $indicateur->id)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }*/
                }
            ],
            'response_data.factuel.*.optionDeReponseId'   => [Rule::requiredIf(!request()->input('response_data.perception')), new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given formulaire.');
                }
            }],
            'response_data.factuel.*.sourceDeVerificationId'        => ['sometimes', Rule::requiredIf(!request()->input('response_data.factuel.*.sourceDeVerification')), 'distinct', new HashValidatorRule(new SourceDeVerification())], 
            'response_data.factuel.*.sourceDeVerification'          => ['sometimes', Rule::requiredIf(!request()->input('response_data.factuel.*.sourceDeVerificationId'))],
            'response_data.factuel.*.preuves'                       => ['sometimes', "array", "min:0"],
            'response_data.factuel.*.preuves.*'                     => ['distinct', "file", 'mimes:doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv|max:20000', "mimetypes:application/pdf,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png|max:20000"],

            'response_data.perception'                              => [Rule::requiredIf(!request()->input('response_data.factuel')), 'array', 'min:1'],
            'response_data.perception.*.indicateurDeGouvernanceId'      => [Rule::requiredIf(!request()->input('response_data.factuel')), 'distinct', new HashValidatorRule(new IndicateurDeGouvernance()), 
                function($attribute, $value, $fail) {
                    $indicateur = IndicateurDeGouvernance::where("type", "perception")->findByKey($value);
                    if (!$indicateur) {
                        // Fail validation if no response options are available
                        $fail("Cet Indicateur n'existe pas.");
                    }

                    $this->indicateurCache = $indicateur;
                    
                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('organisationId', $this->organisationId)->where('indicateurDeGouvernanceId', $indicateur->id)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }
                }
            ],

            'response_data.perception.*.optionDeReponseId'   => [Rule::requiredIf(!request()->input('response_data.factuel')), new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail){
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->indicateurCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given Indicateur.');
                }

            }],
            
            'response_data.perception.commentaire'                => ['nullable', 'string', 'max:255'],
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
