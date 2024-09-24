<?php

namespace App\Http\Requests\audit;

use App\Models\Projet;
use App\Rules\HashValidatorRule;
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
            'annee' => 'sometimes|required',
            'entreprise' => 'sometimes|required',
            'entrepriseContact' => 'sometimes|required',
            'dateDeTransmission' => 'sometimes|required|date|date_format:Y-m-d',
            'etat' =>'sometimes|required',
            'statut' =>'sometimes|required|numeric|min:-1|max:1',
            'projetId' => ['sometimes','required', new HashValidatorRule(new Projet())],
            'rapport' => 'sometimes|required|file|mimes:pdf,docx',
            'categorie' => 'sometimes|required|min:0|max:3'
        ];
    }
}
