<?php

namespace App\Http\Requests\ano;

use App\Models\Bailleur;
use App\Models\TypeAno;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnoRequest extends FormRequest
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
                'dossier' => 'required|max:255',
                'statut' => 'required|integer|min:-2|max:2',
                'dateSoumission' => 'required|date',
                'destinataire' => 'required|string',
                'bailleurId' => ['required', new HashValidatorRule(new Bailleur())],
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
            'dossier.required' => 'Le dossier est obligatoire',
            'dateSoumission.required' => 'La date de soumission est obligatoire',
            'bailleurId.required' => 'Le bailleur est obligatoire',
            'destinataire.required' => 'Le destinataire est obligatoire',
        ];
    }
}
