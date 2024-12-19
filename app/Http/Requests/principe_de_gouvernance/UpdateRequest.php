<?php

namespace App\Http\Requests\principe_de_gouvernance;

use App\Models\PrincipeDeGouvernance;
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
        if(is_string($this->principe_de_gouvernance))
        {
            $this->principe_de_gouvernance = PrincipeDeGouvernance::findByKey($this->principe_de_gouvernance);
        }

        //dd($this->principe_de_gouvernance);

        return [
            'nom'           => ['sometimes', 'string', Rule::unique('principes_de_gouvernance', 'nom')->ignore($this->principe_de_gouvernance)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

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

            // Custom messages for the 'typeDeGouvernanceId' field
            'typeDeGouvernanceId.required' => 'Le champ type de gouvernance est obligatoire.',
        
        ];
    }
}
