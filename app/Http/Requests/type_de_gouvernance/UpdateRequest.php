<?php

namespace App\Http\Requests\type_de_gouvernance;

use App\Models\TypeDeGouvernance;
use App\Models\Programme;
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
        return request()->user()->hasPermissionTo("modifier-un-type-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->type_de_gouvernance))
        {
            $this->type_de_gouvernance = TypeDeGouvernance::findByKey($this->type_de_gouvernance);
        }

        return [
            'nom'  => ['sometimes','max:255', Rule::unique('types_de_gouvernance', 'nom')->where("programmeId", auth()->user()->programmeId)->ignore($this->type_de_gouvernance)->whereNull('deleted_at')],
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
