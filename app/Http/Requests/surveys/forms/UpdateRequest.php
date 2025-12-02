<?php

namespace App\Http\Requests\surveys\forms;

use App\Models\Survey;
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
        return request()->user()->hasPermissionTo("modifier-un-formulaire-individuel") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->survey_form))
        {
            $this->survey_form = Survey::findByKey($this->survey_form);
        }

        return [
            'libelle'               => ['sometimes', 'string', Rule::unique('survey_forms', 'libelle')->ignore($this->survey_form)->where("created_by_type", auth()->user()->profilable_type)->where("created_by_id", auth()->user()->profilable_id)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'description' => 'sometimes|nullable|max:255',
            'form_data' => 'sometimes|array|min:1'
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
