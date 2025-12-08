<?php

namespace App\Http\Requests\unitee_de_mesure;

use App\Models\Unitee;
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
        return request()->user()->hasPermissionTo("modifier-une-unitee-de-mesure") || request()->user()->hasRole("administrateur", "super-admin");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->unitees_de_mesure))
        {
            $this->unitees_de_mesure = Unitee::findByKey($this->unitees_de_mesure);
        }

        return [
            'nom'               => ['sometimes', 'string', Rule::unique('unitees', 'nom')->ignore($this->unitees_de_mesure)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
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
            'nom.unique' => 'Unité de mesure déjà enrégistré.'
        ];
    }
}
