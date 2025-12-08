<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\EvaluationDeGouvernance;
use App\Models\PrincipeDeGouvernance;
use App\Rules\DistinctAttributeRule;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class AjouterObjectifAttenduParPrincipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("modifier-une-evaluation-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
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
            'objectifsAttendu'      => ['required', "array", "min:".$this->evaluation_de_gouvernance->principes_de_gouvernance()->count(), "max:".$this->evaluation_de_gouvernance->principes_de_gouvernance()->count()],
            'objectifsAttendu.*.principeId'   => ['required', 'distinct', new HashValidatorRule(new PrincipeDeGouvernance()), function ($attribute, $value, $fail) {

                // Get the index from the attribute name
                preg_match('/objectifsAttendu\.(\d+)\.principeId/', $attribute, $matches);
                $index = $matches[1] ?? null; // Get the index if it exists
                
                // Ensure each keyId in valeurDeBase is one of the value_keys.id
                if (!in_array(request()->input('objectifsAttendu.*.principeId')[$index], $this->evaluation_de_gouvernance->principes_de_gouvernance()->pluck('id')->toArray())) {
                    $fail("Le principe n'est pas dans cette evaluation.");
                }
    
            }],
            'objectifsAttendu.*.outils'  => 'required|array|min:3|max:3',
            'objectifsAttendu.*.outils.*.type'  => ["required","string","in:factuel,perception,synthetique", new DistinctAttributeRule()],
            'objectifsAttendu.*.outils.*.objectif_attendu'  => 'required|numeric|min:0|max:1',

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
