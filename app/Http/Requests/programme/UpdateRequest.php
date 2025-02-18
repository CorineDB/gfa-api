<?php

namespace App\Http\Requests\programme;

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
        return request()->user()->hasPermissionTo("modifier-un-programme") || request()->user()->hasRole("administrateur", "super-admin");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => ['required','max:255', Rule::unique('programmes')->ignore($this->programme)->whereNull('deleted_at')],
            'code' => ['required', Rule::unique('programmes')->ignore($this->programme)->whereNull('deleted_at')],
            'budgetNational' => 'nullable|int|min:0',
            'objectifGlobaux' => 'required',
            'debut' => 'required|date|date_format:Y-m',
            'fin' => 'required|date|date_format:Y-m|after_or_equal:debut'
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
            'nom.required' => 'Le nom du programme est obligatoire.',
            'objectifGlobaux.required' => 'Veuillez précisez l\'objectif global du programme.',
            'budjetNational.required' => 'Veuillez précisez le budget national du programme.',
            'code.required' => 'Le code du programme est obligatoire.',
            'code.min' => 'Le code du programme ne peut pas être inférieur à 0.',
            'budgetNational.min' => 'Le budget national du programme ne peut pas être inférieur à 0.',
            'debut.required' => 'La date de début du programme est obligatoire.',
            'fin.required' => 'La date de fin du programme est obligatoire.',
        ];
    }
}
