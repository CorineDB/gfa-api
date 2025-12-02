<?php

namespace App\Http\Requests\surveys;

use App\Models\SurveyForm;
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
        return request()->user()->hasPermissionTo("creer-une-enquete-individuelle") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'intitule'               => ['required', 'string', Rule::unique('surveys', 'intitule')->where("surveyable_type", auth()->user()->profilable_type)->where("surveyable_id", auth()->user()->profilable_id)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'description'           => 'nullable|max:255',
            'prive'                 => 'required|boolean:false',
            'surveyFormId'          => ['required', new HashValidatorRule(new SurveyForm())],
            'nbreParticipants'      => ['required', "integer", "min:1"],

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
