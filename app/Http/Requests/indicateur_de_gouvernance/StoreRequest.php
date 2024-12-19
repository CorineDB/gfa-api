<?php

namespace App\Http\Requests\indicateur_de_gouvernance;

use App\Models\CritereDeGouvernance;
use App\Models\OptionDeReponse;
use App\Models\PrincipeDeGouvernance;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("creer-un-indicateur-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Base rules
        return [
            'nom'   => ['required', 'string', Rule::unique('indicateurs_de_gouvernance', 'nom')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'type'                      => 'required|string|in:factuel,perception',  // Ensures the value is either 'factuel' or 'perception'
            'description'               => 'nullable|max:255',
            //'can_have_multiple_reponse' => ['sometimes','boolean'],
            /*'options_de_reponse'        => ['required', 'array', 'min:2'],
            'options_de_reponse.*'      => ['required', 'distinct', new HashValidatorRule(new OptionDeReponse())],
            'principeable_id'           => ['required',
                                            function ($attribute, $value, $fail) {
                                                if ($this->type === 'perception') {
                                                    $validatorRule = new HashValidatorRule(new PrincipeDeGouvernance());
                                                } elseif ($this->type === 'factuel') {
                                                    $validatorRule = new HashValidatorRule(new CritereDeGouvernance());
                                                } else {
                                                    $fail('Invalid type specified for validation.');
                                                    return;
                                                }
                                                
                                                // Apply the validator rule manually
                                                if (!$validatorRule->passes($attribute, $value)) {
                                                    $fail($validatorRule->message());
                                                }
                                            }
                                        ]*/
        ];

        // Conditionally apply validation based on 'type'
        if ($this->type === 'perception') {
            $rules['principeable_id'] = ['required', new HashValidatorRule(new PrincipeDeGouvernance())];
        } elseif ($this->type === 'factuel') {
            $rules['principeable_id'] = ['required', new HashValidatorRule(new CritereDeGouvernance())];
        }

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
            'nom.required'                      => "Le champ nom est obligatoire.",
            "nom.max"                           => "Le nom ne doit pas dépasser 255 caractères.",
            "nom.unique"                        => "Ce nom est déjà utilisé.",
            "type.required"                     => "Le type d'indicateur est obligatoire.",
            "type.in"                           => "Le type doit être soit factuel, soit perception.",
            "principeable_id.required"          => "Le champ principeable ID est obligatoire.",
            "principeable_id.exists"            => "L'ID fourni n'existe pas dans la table liée.",
            "can_have_multiple_reponse.boolean" => "La valeur de ce champ doit être vraie ou fausse.",
            
            // Custom messages for the 'description' field
            "description.max"       => "La description ne doit pas dépasser 255 caractères.",

        ];
    }
}
