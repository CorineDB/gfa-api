<?php

namespace App\Http\Requests\fonds;

use App\Models\CritereDeGouvernance;
use App\Models\Fond;
use App\Models\OptionDeReponse;
use App\Models\Programme;
use App\Models\SourceDeVerification;
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
        return request()->user()->hasPermissionTo("modifier-un-fond") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->fond))
        {
            $this->fond = Fond::findByKey($this->fond);
        }

        return [
            'nom_du_fond'               => ['sometimes', 'string', Rule::unique('fonds', 'nom_du_fond')->ignore($this->fond)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            'fondDisponible' => 'sometimes|integer|min:0|max:9999999999999'
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
