<?php

namespace App\Http\Requests\entreprise_institution;

use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'       => ['required','max:255', Rule::unique('users')->where('type', 'institution')->whereNull('deleted_at')],
            'contact'   => ['required','numeric', 'digits_between:8,24', Rule::unique('users')->whereNull('deleted_at')],
            'email'     => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'programmeId'   => ['required', new HashValidatorRule(new Programme())],
        ];
    }
}
