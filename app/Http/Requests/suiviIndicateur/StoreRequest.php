<?php

namespace App\Http\Requests\suiviIndicateur;

use App\Models\Indicateur;
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
            'indicateurId'  => ['required', new HashValidatorRule(new Indicateur())]
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
            'valeurCible.required' => 'Veuillez préciser la valeur cible de cette année de suivi.',
            'valeurRealise.required' => 'Veuillez précisez la valeur réalisé.',
            'commentaire.required' => 'Veuillez faire un commentaire du suivi réalisé.',
            'indicateurId.required'              => 'Veuillez préciser le indicateur auquelle sera associé le suivi .',
            'indicateurId.exists'                => 'Indicateur inexistant. Veuillez préciser un indicateur existant.'
        ];
    }/*

    protected function prepareForValidation(): void
    {

        $indicateur = Indicateur::decodeKey($this->indicateurId);

        if(!$indicateur)
            throw ValidationException::withMessages(['indicateurId' => "Indicateur inconnue"]);

        $this->merge([
            'indicateurId' => $indicateur
        ]);
    }  */
}
