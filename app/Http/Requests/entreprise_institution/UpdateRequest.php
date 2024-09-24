<?php

namespace App\Http\Requests\entreprise_institution;

use App\Models\Programme;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        if(is_string($this->institution))
        {
            $this->institution = User::findByKey($this->institution);
        }

        return [
            "nom"       => ['nullable','max:255', Rule::unique('users')->ignore($this->institution->id)->where('type', 'institution')->whereNull('deleted_at')],
            "contact"   => ['nullable','numeric', 'digits_between:8,24', Rule::unique('users')->ignore($this->institution->id)->whereNull('deleted_at')],
            "email"     => ['nullable','max:255', Rule::unique('users')->ignore($this->institution->id)->whereNull('deleted_at')],
            'programmeId'   => ['required', new HashValidatorRule(new Programme())],
        ];
    }
}
