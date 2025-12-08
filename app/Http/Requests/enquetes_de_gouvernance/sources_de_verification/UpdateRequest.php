<?php

namespace App\Http\Requests\enquetes_de_gouvernance\sources_de_verification;

use App\Models\enquetes_de_gouvernance\SourceDeVerification;
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
        return request()->user()->hasPermissionTo("modifier-une-source-de-verification") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->source_de_verification))
        {
            $this->source_de_verification = SourceDeVerification::findByKey($this->source_de_verification);
        }

        return [
            'intitule'               => ['sometimes', 'string', Rule::unique('sources_de_verification', 'intitule')->ignore($this->source_de_verification)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
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
