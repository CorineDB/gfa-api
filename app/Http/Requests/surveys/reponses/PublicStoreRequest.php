<?php

namespace App\Http\Requests\surveys\reponses;

use App\Models\Survey;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class PublicStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $survey = null;
        if (request()->input('surveyId')) {
            $survey = Survey::findByKey(request()->input('surveyId'));
        }

        return $survey && ($survey->statut == 0 && !$survey->privee);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'response_data'     => 'required|array|min:1',
            'surveyId'          => ['required', new HashValidatorRule(new Survey())],
            'idParticipant'     => ['required', "string"],
            'commentaire'       => ['nullable', "string"]
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
