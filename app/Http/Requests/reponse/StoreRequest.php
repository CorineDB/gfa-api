<?php

namespace App\Http\Requests\reponse;

use App\Models\Formulaire;
use App\Models\Question;
use App\Models\User;
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
            'valeur'                => 'required|max:255',

            'date'                  => 'required',

            'questionId' => ['required', new HashValidatorRule(new Question())],

            'formulaireId' => ['required', new HashValidatorRule(new Formulaire())],

            'shared' => 'sometimes|required|array'
        ];
    }
}
