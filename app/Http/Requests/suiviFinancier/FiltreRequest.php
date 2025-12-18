<?php

namespace App\Http\Requests\suiviFinancier;

use App\Models\Activite;
use App\Models\Projet;
use App\Models\Bailleur;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class FiltreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("voir-un-suivi-financier") || request()->user()->hasRole("unitee-de-gestion", "organisation");

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
            'bailleurId'   => ['sometimes', new HashValidatorRule(new Bailleur())],
            'projetId'   => ['sometimes', new HashValidatorRule(new Projet())],
            'activiteId'   => ['sometimes', new HashValidatorRule(new Activite())],
            'trimestre' => 'required|min:1|max:4',
            'annee' => 'required'
        ];
    }
}
