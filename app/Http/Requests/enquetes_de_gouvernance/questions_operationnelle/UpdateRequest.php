<?php

namespace App\Http\Requests\enquetes_de_gouvernance\questions_operationnelle;

use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
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
        return request()->user()->hasPermissionTo("modifier-un-indicateur-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->question_operationnelle))
        {
            $this->question_operationnelle = QuestionOperationnelle::findByKey($this->question_operationnelle);
        }

        return [
            'nom'  => ['sometimes','max:255', Rule::unique('questions_operationnelle', 'nom')->where("programmeId", auth()->user()->programmeId)->ignore($this->question_operationnelle)->whereNull('deleted_at')],
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
