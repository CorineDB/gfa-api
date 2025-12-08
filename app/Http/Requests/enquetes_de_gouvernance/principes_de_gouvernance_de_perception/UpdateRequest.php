<?php

namespace App\Http\Requests\enquetes_de_gouvernance\principes_de_gouvernance_de_perception;

use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
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
        return request()->user()->hasPermissionTo("modifier-un-principe-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->principe_de_perception))
        {
            $this->principe_de_perception = PrincipeDeGouvernancePerception::findByKey($this->principe_de_perception);
        }

        return [
            'nom'  => ['sometimes','max:255', Rule::unique('principes_de_gouvernance_de_perception', 'nom')->where("programmeId", auth()->user()->programmeId)->ignore($this->principe_de_perception)->whereNull('deleted_at')],
            'description' => 'sometimes|nullable|max:255'
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

            // Custom messages for the 'programmeId' field
            'programmeId.required' => 'Le champ programme est obligatoire.',

        ];
    }
}
