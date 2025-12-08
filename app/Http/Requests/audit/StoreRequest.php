<?php

namespace App\Http\Requests\audit;

use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
            'annee' => 'required',
            'entreprise' => 'required',
            'entrepriseContact' => 'required',
            'dateDeTransmission' => 'required|date|date_format:Y-m-d',
            'etat' =>'required',
            'statut' =>'required|numeric|min:-1|max:1',
            'projetId' => [ 'required', new HashValidatorRule(new Projet())],
            //'rapport' => 'required|file|mimes:pdf,docx',
            'categorie' => 'required|min:0|max:3'
        ];
    }
}
