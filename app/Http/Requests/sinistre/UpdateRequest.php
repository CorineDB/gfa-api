<?php

namespace App\Http\Requests\sinistre;

use App\Models\Bailleur;
use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'nom'     => 'sometimes|required',
            'sexe' => 'sometimes|required',
            'contact' => 'sometimes|required',
            'longitude' => 'sometimes|required',
            'latitude' => 'sometimes|required',
            'montant' => 'sometimes|required',
            'rue' => 'sometimes|required',
            'referencePieceIdentite' => 'sometimes|required',
            'statut' => 'sometimes|required',
            'payer' => 'sometimes|required|integer',
            'modeDePaiement' => 'sometimes|required',
            'dateDePaiement' => 'sometimes|required|date|date_format:Y-m-d'
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
            'nom.required'                    => 'Le nom du sinistre est obligatoire.',
            'prenoms.required'                => 'Les prénoms du sinistre sont obligatoire.',
            'contact.required'                => 'Le contact du sinistre est obligatoire.',
            'annee.required'                  => 'Veuillez préciser L\'année est obligatoire.',
        ];
    }
}
