<?php

namespace App\Http\Requests\ano;

use App\Models\Bailleur;
use App\Models\TypeAno;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
            return [
                'dossier' => 'sometimes|required|max:255',
                'dateSoumission' => 'sometimes|required|date',
                'destinataire' => 'sometimes|required|string',
                'bailleurId' => ['sometimes', 'required', new HashValidatorRule(new Bailleur())],
                'commentaire' => 'sometimes|required'
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
