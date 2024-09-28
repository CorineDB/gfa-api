<?php

namespace App\Http\Requests\enquete_de_collecte;

use App\Models\Enquete;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollecteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasRole("administrateur", "super-admin", "unitee-de-gestion");
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
            //'enqueteDeCollecteId'   => ['required', new HashValidatorRule(new Enquete())],
            'userId'   => ['required', new HashValidatorRule(new User())],
            'indicateurDeGouvernanceId'   => [
                'required', 
                new HashValidatorRule(new IndicateurDeGouvernance()), 
                function($attribute, $value, $fail) {
                    $indicateur = IndicateurDeGouvernance::findByKey($value);
                    
                    // Check if there are response options
                    if (count($indicateur->options_de_reponse) == 0) {
                        // Fail validation if no response options are available
                        $fail('The selected Indicateur does not have any response options.');
                    }
                }, 
                function($attribute, $value, $fail) {
                    $indicateur = IndicateurDeGouvernance::findByKey($value);
                    
                    //dd($this->enquete_de_collecte->id);
                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('userId', $this->userId)->where('indicateurDeGouvernanceId', $this->indicateurDeGouvernanceId)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }
                }
            ],
            'optionDeReponseId'   => ['required', new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail){

                $indicateur = IndicateurDeGouvernance::findByKey($this->indicateurDeGouvernanceId);

                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 * 
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($indicateur->options_de_reponse()->where('optionId', $this->optionDeReponseId)->exists())) {
                    $fail('The selected option is invalid for the given Indicateur.');
                }

            }],
            'source'                => [
                function($attribute, $value, $fail) {
                    $indicateur = IndicateurDeGouvernance::findByKey($this->indicateurDeGouvernanceId);
                    
                    // Check if 'type' is 'factuel' and make 'source' required in that case
                    if ($indicateur->type === 'factuel' && empty($value)) {
                        $fail('The source field is required for factuel indicateurs.');
                    }
                },
                'sometimes', 'string', 'max:255'
            ],
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
