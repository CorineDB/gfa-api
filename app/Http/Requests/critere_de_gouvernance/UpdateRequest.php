<?php

namespace App\Http\Requests\critere_de_gouvernance;

use App\Models\CritereDeGouvernance;
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
        return request()->user()->hasRole("administrateur", "super-admin", "unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->critere_de_gouvernance))
        {
            $this->critere_de_gouvernance = CritereDeGouvernance::findByKey($this->critere_de_gouvernance);
        }

        return [
            'nom'  => ['sometimes','max:255', Rule::unique('criteres_de_gouvernance', 'nom')->ignore($this->critere_de_gouvernance)->whereNull('deleted_at')],
            'description' => 'sometimes|nullable|max:255',
            'principeDeGouvernanceId'   => ['sometimes', new HashValidatorRule(new PrincipeDeGouvernance())]
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
