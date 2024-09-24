<?php

namespace App\Http\Requests\esuivi;

use App\Models\EntrepriseExecutant;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class FormulaireRequest extends FormRequest
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
            'type' => 'required',
            'typeId' => 'required',
            'entrepriseExecutantId' => ['required', new HashValidatorRule(new EntrepriseExecutant())],

        ];
    }
}
