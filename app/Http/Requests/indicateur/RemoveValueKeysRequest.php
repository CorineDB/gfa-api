<?php

namespace App\Http\Requests\indicateur;

use App\Models\Indicateur;
use App\Models\IndicateurValueKey;
use App\Models\Unitee;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\ValidationException;

class RemoveValueKeysRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("supprimer-une-cle-de-valeur-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->indicateur))
        {
            $this->indicateur = Indicateur::findByKey($this->indicateur);
        }

        if($this->indicateur->suivis->isNotEmpty()) throw ValidationException::withMessages(["Cet indicateur a deja ete suivi et donc ne peut plus etre mis a jour."]);
        
        return [
            
            'value_keys'                    => ["array", "min:1"],
            'value_keys.*'               => ["required", "string", 'distinct', new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {
                if (!$this->indicateur->valueKeys()->where('indicateurValueKeyId', request()->input($attribute))->exists()) {
                    $fail("Cette cle est pas rattacher a l'indicateur.");
                }
            }]
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
            'nom.required'          => 'Le nom de l\'indicateur est obligatoire.',
            'description.required'  => 'La description de l\'indicateur est obligatoire.',
            'anneeDeBase.required'  => 'L\'annee de base est obligatoire.',
            'valeurDeBase.required' => 'La valeur de base est obligatoire.',
            'unites.required'       => 'Veuillez préciser les unites de mésure de l\'indicateur',
            'unites.array'          => 'Veuillez préciser les unites de mesure de l\'indicateur dans un tableau',
            'categorieId.exists'    => 'Catégorie inconnu. Veuillez sélectionner une catégorie existant dans le système',
            'bailleurId.required'   => 'Veuillez préciser le bailleur.',
            'bailleurId.exists'     => 'Bailleur inconnu. Veuillez sélectionner un bailleur existant dans le système',
            'uniteeMesureId.required'   => 'Veuillez préciser l\'unitée de mésure.',
            'uniteeMesureId.exists'     => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système',
        ];
    }
}
