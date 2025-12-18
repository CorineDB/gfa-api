<?php

namespace App\Http\Requests\sinistre;

use App\Models\Programme;
use App\Models\Site;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

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
            'nom'     => 'required',
            'sexe' => 'required',
            'contact' => 'required',
            'montant' => 'required',
            'rue' => 'required',
            'referencePieceIdentite' => 'required',
            'statut' => 'required',
            'payer' => 'required|integer',
            'modeDePaiement' => 'required',
            'dateDePaiement' => 'required|date|date_format:Y-m-d',
            'siteId' => ['required', new HashValidatorRule(new Site())],
            'programmeId' => ['required', new HashValidatorRule(new Programme())],
        ];
    }


}
