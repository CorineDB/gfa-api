<?php

namespace App\Http\Requests\organisation;

use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class GenererRequest extends FormRequest
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
            'annee' => 'required'
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
            'programmeId.required' => 'L\'id du programme est obligatoire',
            'annee.required' => 'L\'année est obligatoire',
        ];
    }
}
