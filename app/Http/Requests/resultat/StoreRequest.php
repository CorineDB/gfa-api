<?php

namespace App\Http\Requests\resultat;

use App\Models\Indicateur;
use App\Models\Programme;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'nom' => 'required',
            'description' => 'required',
            'indicateurId' => ['required', new HashValidatorRule(new Indicateur())],
            'programmeId' => ['sometimes', 'required', new HashValidatorRule(new Programme())],
            'projetId' => ['sometimes', 'required', new HashValidatorRule(new Projet())],
        ];
    }

}
