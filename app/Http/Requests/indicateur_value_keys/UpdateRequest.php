<?php

namespace App\Http\Requests\indicateur_value_keys;

use App\Models\IndicateurValueKey;
use App\Models\Unitee;
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
        return request()->user()->hasPermissionTo("modifier-une-cle-de-valeur-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->indicateur_value_key))
        {
            $this->indicateur_value_key = IndicateurValueKey::findByKey($this->indicateur_value_key);
        }

        return [
            'libelle'                       => ['sometimes', 'string', Rule::unique('indicateur_value_keys', 'libelle')->ignore($this->indicateur_value_key)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            "key"                           => ["sometimes", "max:255", Rule::unique('indicateur_value_keys', 'key')->where("programmeId", auth()->user()->programmeId)->ignore($this->indicateur_value_key)->whereNull('deleted_at')],

            'description'                   => 'nullable|max:255',
            'uniteeMesureId'                => ['sometimes', new HashValidatorRule(new Unitee())],
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
            // Custom messages for the 'libelle' field
            'libelle.required'      => 'Le champ libelle est obligatoire.',
            'libelle.max'           => 'Le libelle ne doit pas dépasser 255 caractères.',
            'libelle.unique'        => 'Ce libelle est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'programmeId' field
            'programmeId.required' => 'Le champ programme est obligatoire.',
        ];
    }
}
