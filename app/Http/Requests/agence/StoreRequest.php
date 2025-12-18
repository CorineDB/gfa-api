<?php

namespace App\Http\Requests\agence;

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
            'nom'       => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'contact'   => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'email'     => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')]
        ];
    }
}
