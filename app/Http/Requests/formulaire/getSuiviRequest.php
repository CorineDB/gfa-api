<?php

namespace App\Http\Requests\formulaire;

use App\Models\EntrepriseExecutant;
use App\Models\Formulaire;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class getSuiviRequest extends FormRequest
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
            'userId' => ['sometimes','required', new HashValidatorRule(new User())],
            'entrepriseId' => ['sometimes','required', new HashValidatorRule(new EntrepriseExecutant())],
            'formulaireId' => ['required', new HashValidatorRule(new Formulaire())],
            'date' => 'sometimes|required'
        ];
    }
}
