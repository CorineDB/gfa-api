<?php

namespace App\Http\Requests\unitee_de_mesure;

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
            'nom' => ['required', 'max:255', Rule::unique('unitees','nom')->whereNull('deleted_at')],
            'nom' => 'required|max:255|unique:unitees,nom',
            'type' => 'required|min:0|max:1'
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
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'L\'unité de mesure est déjà enrégistré.'
        ];
    }
}
