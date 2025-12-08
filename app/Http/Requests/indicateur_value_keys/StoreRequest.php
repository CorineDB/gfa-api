<?php

namespace App\Http\Requests\indicateur_value_keys;

use App\Models\Unitee;
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
        return request()->user()->hasPermissionTo("creer-une-cle-de-valeur-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'libelle'                       => ['required', 'string', Rule::unique('indicateur_value_keys', 'libelle')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            "key" => ["required", "max:255", Rule::unique('indicateur_value_keys', 'key')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            "description" => "nullable", "max:255",
            "uniteeMesureId"   => ["required", new HashValidatorRule(new Unitee())]
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
