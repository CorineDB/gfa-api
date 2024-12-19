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
        return request()->user()->hasPermissionTo("creer-une-unitee-de-mesure") || request()->user()->hasRole("administrateur", "super-admin");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'               => ['required', 'string', Rule::unique('unitees', 'nom')->ignore($this->survey)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

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
