<?php

namespace App\Http\Requests\surveys;

use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\SurveyForm;
use App\Rules\HashValidatorRule;
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
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->survey))
        {
            $this->survey = Survey::findByKey($this->survey);
        }

        return [
            'libelle'               => ['sometimes','max:255', Rule::unique('surveys', 'libelle')->ignore($this->survey)->whereNull('deleted_at')],
            'description'           => 'sometimes|nullable|max:255',
            'surveyFormId'          => ['required', new HashValidatorRule(new SurveyForm())],
            'nbreParticipants'      => ["integer", "min:1"],
            'debut'                 => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before:fin'
            ],
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut',
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
