<?php

namespace App\Http\Requests\enquete_de_collecte;

use App\Models\Enquete;
use App\Models\Organisation;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CollecteRequest extends FormRequest
{
    protected $indicateurCache = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->enquete_de_collecte))
        {
            $this->enquete_de_collecte = Enquete::findByKey($this->enquete_de_collecte);
        }

        return [
            'organisationId'   => ['sometimes', Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],


            'response_data'        => ['required', 'array', 'min:1'],
            'response_data.factuel'      => ['sometimes', Rule::requiredIf(request()->input('response_data.perception')), 'array', 'min:1'],

            'response_data.factuel.*.indicateurDeGouvernanceId'      => ['required', 'distinct', 
                new HashValidatorRule(new IndicateurDeGouvernance()), 
                function($attribute, $value, $fail) {
                    $indicateur = IndicateurDeGouvernance::where("type", "factuel")->findByKey($value);
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
            'response_data.factuel.*.optionDeReponseId'   => ['required', new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->indicateurCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given Indicateur.');
                }

            }],
            'response_data.factuel.*.source'                => ['required', 'string', 'max:255'],
            
            'response_data.perception'      => ['sometimes', Rule::requiredIf(request()->input('response_data.factuel')), 'array', 'min:1'],
            'response_data.perception.*.indicateurDeGouvernanceId'      => ['required', 'distinct', new HashValidatorRule(new IndicateurDeGouvernance()), 
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

            'response_data.perception.*.optionDeReponseId'   => ['required', new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail){
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
