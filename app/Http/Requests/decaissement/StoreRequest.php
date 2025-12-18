<?php

namespace App\Http\Requests\decaissement;

use App\Models\EntrepriseExecutant;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
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
        return request()->user()->hasPermissionTo("creer-un-decaissement") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'projetId' => ['required', new HashValidatorRule(new Projet())],
            'montant' => 'required|min:0',
            'date' => 'required|date|date_format:Y-m-d',
            'type' => 'required|integer|min:0|max:1',
            'methodeDePaiement' => 'required|min:0|max:3',
            'beneficiaireId' => ['required_if:methodeDePaiement,0', new HashValidatorRule(new EntrepriseExecutant())],
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
            'projetId.required' => 'Le projet est obligatoire.',
            'montant.required' => 'Le montant est obligatoire est obligatoire.',
            'beneficiaireId.required_if' => 'Le beneficiare est obligatoire quand la méthode de paiement est paiement direct.',
            'date.required' => 'La date est obligatoire',
            'date.date_format' => 'La date doit etre sous le format annee-mois-jour'
        ];
    }
}
