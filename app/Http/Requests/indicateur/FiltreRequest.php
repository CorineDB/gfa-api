<?php

namespace App\Http\Requests\indicateur;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\Unitee;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FiltreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::user();

        return $user->hasRole("unitee-de-gestion", "bailleur");    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'uniteeMesureId'   => ['sometimes', new HashValidatorRule(new Unitee())],

            'categorieId'   => ['sometimes', new HashValidatorRule(new Categorie())],

            'bailleurId'    => ['sometimes', Rule::requiredIf(request()->user()->hasRole(['unitee-de-gestion'])), new HashValidatorRule(new Bailleur())]

        ];
    }
}
