<?php

namespace App\Http\Requests\eSuiviActiviteMod;

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
            'description' => 'required',
            'niveauDeMiseEnOeuvre' => 'required|integer',
            'eActiviteModId' => 'required',
            'commentaire' => 'sometimes'
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
            'description.required' => 'La description est obligatoire',
            'niveauDeMiseEnOeuvre.required' => 'Le niveau de mise en oeuvre est obligatoire',
            'eActiviteModId.required' => 'Le mod est obligatoire'
        ];
    }
}
