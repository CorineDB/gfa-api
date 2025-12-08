<?php

namespace App\Http\Requests\objectifSpecifique;

use App\Models\Indicateur;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        ];
    }
}
