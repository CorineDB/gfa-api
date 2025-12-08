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
        return request()->user()->hasPermissionTo("modifier-une-enquete-individuelle") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (is_string($this->survey)) {
            $this->survey = Survey::findByKey($this->survey);
        }

        return [
            'intitule'               => ['sometimes', 'string', Rule::unique('surveys', 'intitule')->ignore($this->survey)->where("created_by_type", auth()->user()->profilable_type)->where("created_by_id", auth()->user()->profilable_id)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

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

            // ---------- INTITULÉ ----------
            'intitule.required' => 'L’intitulé de l’enquête personnalisée est obligatoire.',
            'intitule.string'   => 'L’intitulé de l’enquête personnalisée doit être une chaîne de caractères valide.',
            'intitule.unique'   => 'Une enquête personnalisée portant cet intitulé existe déjà.',

            // ---------- DESCRIPTION ----------
            'description.max'   => 'La description de l’enquête personnalisée ne peut pas dépasser 255 caractères.',

            // ---------- PRIVÉ ----------
            'prive.required'    => 'Veuillez indiquer si l’enquête personnalisée est privée ou publique.',
            'prive.boolean'     => 'Le champ indiquant si l’enquête est privée doit être un booléen.',

            // ---------- FORMULAIRE ----------
            'surveyFormId.required' => 'Le formulaire associé à l’enquête personnalisée est obligatoire.',
            'surveyFormId.*'        => 'Le formulaire sélectionné pour cette enquête personnalisée est invalide.',

            // ---------- PARTICIPANTS ----------
            'nbreParticipants.required' => 'Le nombre de participants prévu pour l’enquête personnalisée est obligatoire.',
            'nbreParticipants.integer'  => 'Le nombre de participants doit être un nombre entier.',
            'nbreParticipants.min'      => 'Le nombre de participants doit être au moins égal à 1.',

            // ---------- DATES ----------
            'debut.required'      => 'La date de début de l’enquête personnalisée est obligatoire.',
            'debut.date'          => 'La date de début doit être une date valide.',
            'debut.date_format'   => 'La date de début doit respecter le format AAAA-MM-JJ.',
            'debut.before'        => 'La date de début de l’enquête personnalisée doit précéder la date de fin.',

            'fin.required'        => 'La date de fin de l’enquête personnalisée est obligatoire.',
            'fin.date'            => 'La date de fin doit être une date valide.',
            'fin.date_format'     => 'La date de fin doit respecter le format AAAA-MM-JJ.',
            'fin.after_or_equal'  => 'La date de fin doit être postérieure ou égale à la date de début de l’enquête personnalisée.',
        ];
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
