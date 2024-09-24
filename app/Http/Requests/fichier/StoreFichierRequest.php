<?php

namespace App\Http\Requests\fichier;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichierRequest extends FormRequest
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
            'fichier' => 'required|file',
            'projetId' => 'sometimes|required',
            'anoId' => 'sometimes|required',
            'reponseAnoId' => 'sometimes|required',
            'sharedId' => 'sometimes|required|array',
            'autre' => 'sometimes|required'
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
            'nom.required' => 'Le nom du fichier est obligatoire.',
            'chemin.required' => 'Le chemin du fichier est obligatoire.',
            'userId.required' => 'L\'auteur du fichier est obligatoire'
        ];
    }
}
