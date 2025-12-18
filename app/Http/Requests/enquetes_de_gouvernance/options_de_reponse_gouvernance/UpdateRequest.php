<?php

namespace App\Http\Requests\enquetes_de_gouvernance\options_de_reponse_gouvernance;

use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
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
        return request()->user()->hasPermissionTo("modifier-une-option-de-reponse") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->option_de_reponse_gouvernance))
        {
            $this->option_de_reponse_gouvernance = OptionDeReponseGouvernance::findByKey($this->option_de_reponse_gouvernance);
        }

        return [
            'libelle'  => ['sometimes','max:255', Rule::unique('options_de_reponse_gouvernance', 'libelle')->where('type', $this->input('type'))->where("programmeId", auth()->user()->programmeId)->ignore($this->option_de_reponse_gouvernance)->whereNull('deleted_at')],

            'type'          => 'required|string|in:factuel,perception',  // Ensures the value is either 'factuel' or 'perception'
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
