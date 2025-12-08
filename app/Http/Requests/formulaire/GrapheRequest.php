<?php

namespace App\Http\Requests\formulaire;

use App\Models\Formulaire;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class GrapheRequest extends FormRequest
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
            'formulaireId' => ['required', new HashValidatorRule(new Formulaire())],
            'date' => 'sometimes|required|date'
        ];
    }
}
