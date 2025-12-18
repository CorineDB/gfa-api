<?php

namespace App\Http\Requests\mod;

use App\Models\MOD;
use App\Models\Programme;
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
        if(is_string($this->mod))
        {
            $this->mod = MOD::findByKey($this->mod);
        }

        return [
            'nom'       => ['required','max:255', Rule::unique('users')->ignore($this->mod->user)->whereNull('deleted_at')],
            'contact'   => ['required', 'numeric', 'digits_between:8,24', Rule::unique('users')->ignore($this->mod->user)->whereNull('deleted_at')],
            'email'     => ['required','email','max:255', Rule::unique('users')->ignore($this->mod->user)->whereNull('deleted_at')],
            'programmeId'   => ['required', new HashValidatorRule(new Programme())]
        ];
    }
}
