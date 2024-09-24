<?php

namespace App\Http\Requests\suivi_indicateur_mod;

use App\Models\Indicateur;
use App\Models\IndicateurMod;
use App\Models\ValeurCibleIndicateur;
use App\Rules\HashValidatorRule;
use App\Rules\YearValidationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'annee'         => 'required|date_format:Y|gte:1940',
            'dateSuivie'    => [Rule::requiredIf(!request('trimestre')), 'date_format:Y-m-d', new YearValidationRule, function(){
                $this->merge([
                    "trimestre" => Carbon::parse(request('dateSuivie'))->quarter
                ]);
            }],

            'trimestre'     =>  'integer|min:1|max:4',

            'valeurCible'   => [Rule::requiredIf(ValeurCibleIndicateur::where('cibleable_id', $this->indicateurId)->where('annee', $this->annee)->first() == null),'array','min:1'],
            'valeurRealise' => 'required|array|min:1',
            'commentaire'          => 'sometimes',
            'indicateurModId'               => ['required', new HashValidatorRule(new IndicateurMod())]
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
            'trimestre.required' => 'Veuillez préciser le trimestre sur lequel le suivi doit être réalisé.',
            'trimestre.min' => 'La valeur minimal pour le trimestre est 1',
            'trimestre.max' => 'La valeur maximal pour le trimestre est 4',
            'annee.required' => 'Veuillez préciser l\'année du suivi.',
            'valeurCible.sometimes' => 'Veuillez préciser la valeur cible.',
            'valeurRealise.required' => 'Veuillez précisez la valeur réalisé.',
            'commentaire.required' => 'Veuillez faire un commentaire du suivi réalisé.',
            'indicateurModId.required'              => 'Veuillez préciser le indicateur du mod auquelle sera associé le suivi .',
            'indicateurModId.exists'                => 'Indicateur mod inexistant. Veuillez préciser un indicateur mod existant.'
        ];
    }
}
