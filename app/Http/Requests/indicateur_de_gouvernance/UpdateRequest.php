<?php

namespace App\Http\Requests\indicateur_de_gouvernance;

use App\Models\CritereDeGouvernance;
use App\Models\IndicateurDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\PrincipeDeGouvernance;
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
        return request()->user()->hasPermissionTo("modifier-un-indicateur-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->indicateur_de_gouvernance))
        {
            $this->indicateur_de_gouvernance = IndicateurDeGouvernance::findByKey($this->indicateur_de_gouvernance);
        }

        // Base rules
        $rules = [
            'nom'                       => ['sometimes', 'string', Rule::unique('indicateurs_de_gouvernance', 'nom')->ignore($this->indicateur_de_gouvernance)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'type'                      => 'sometimes|string|in:factuel,perception',  // Ensures the value is either 'factuel' or 'perception'
            'description'               => 'sometimes|nullable|max:255',
            /*'can_have_multiple_reponse' => 'sometimes|boolean',
            'options_de_reponse'        => ['sometimes', 'array', 'min:2'],
            'options_de_reponse.*'      => ['required', 'distinct', new HashValidatorRule(new OptionDeReponse())]*/
        ];

        // Conditionally apply validation based on 'type'
        /*if ($this->type === 'perception') {
            $rules['principeable_id'] = ['required', new HashValidatorRule(new PrincipeDeGouvernance())];
        } elseif ($this->type === 'factuel') {
            $rules['principeable_id'] = ['required', new HashValidatorRule(new CritereDeGouvernance())];
        }*/

        return $rules;
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
            'nom.required'                      => 'Le champ nom est obligatoire.',
            'nom.max'                           => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique'                        => 'Ce nom est déjà utilisé.',
            'type.required'                     => 'Le type est obligatoire.',
            'type.in'                           => 'Le type doit être soit factuel, soit perception.',
            'principeable_id.required'          => 'Le champ principeable ID est obligatoire.',
            'principeable_id.exists'            => 'L\'ID fourni n\'existe pas dans la table liée.',
            'can_have_multiple_reponse.boolean' => 'La valeur de ce champ doit être vraie ou fausse.',

        ];
    }
}
