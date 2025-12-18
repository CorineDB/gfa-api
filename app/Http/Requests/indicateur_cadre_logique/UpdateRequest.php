<?php

namespace App\Http\Requests\indicateur_cadre_logique;

use App\Models\ObjectifSpecifique;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\Resultat;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        return [
            'nom'               => 'bail|required|max:255',

            'sourceDeVerification'=> 'bail|required|max:255',

            'hypothese'=> 'bail|required|max:255',

            //"type"              => ['bail','required', Rule::in(['programme','projet','resultat','objectif_speficique'])]
            'programmeId'   => ['sometimes','required', new HashValidatorRule(new Programme())],

            'projetId'   => ['sometimes','required', new HashValidatorRule(new Projet())],

            'resultatId'   => ['sometimes','required', new HashValidatorRule(new Resultat())],

            'objectifId'   => ['sometimes','required', new HashValidatorRule(new ObjectifSpecifique())],

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
            'nom.required'              => 'Le nom de l\'indicateur est obligatoire.',
            'nom.max'                   => 'Le nom de l\'indicateur doit être au maximun de 255 caractère.',
            'type.required'             => 'Veuillez préciser le type d\'indicateur.',
            'type.in'                   => 'Veuillez préciser un type d\'indicateur se trouvant dans cette liste "programmes","projets","resultats","objectifs_speficique".',
            'indicatable_id.required'   => 'Veuillez préciser le type d\'indicateur.'
        ];
    }
}
