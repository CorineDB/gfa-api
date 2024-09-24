<?php

namespace App\Http\Requests\site;

use App\Models\Bailleur;
use App\Models\EntrepriseExecutant;
use App\Models\Programme;
use App\Models\Site;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::user();

        return $user->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->site))
        {
            $this->site = Site::findByKey($this->site);
        }

        return [

            'nom'           => ['sometimes','required','max:255', Rule::unique('sites', 'nom')->ignore($this->site->id)->whereNull('deleted_at')],

            'longitude'     => 'sometimes|required|max:255',

            'latitude'      => 'sometimes|required|max:255',

            /*'bailleurId' => ['sometimes|required', new HashValidatorRule(new Bailleur())],

            'entrepriseExecutanteId.*' => 'required'*/
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
            'nom.required'                      => 'Le nom de l\'indicateur est obligatoire.',
            'longitude.required'                => 'La longitude du site est obligatoire.',
            'latitude.required'                 => 'La latitude du site est obligatoire.',

            'bailleurId.required'               => 'Veuillez préciser le bailleur qui finance les travaux de ce site pour ce programme.',
            'bailleurId.exists'                 => 'Bailleur inconnu. Veuillez sélectionner un bailleur dans le système',

            'programmeId.required'               => 'Veuillez préciser le programme.',
            'programmeId.exists'                 => 'Programme inconnu. Veuillez sélectionner un programme existant'
        ];
    }
}
