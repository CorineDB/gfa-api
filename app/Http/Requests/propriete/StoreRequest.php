<?php

namespace App\Http\Requests\propriete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Sinistre;
use App\Rules\HashValidatorRule;

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
            'nom'           => 'required',
            'longitude'     => 'required',
            'latitude'      => 'required',
            'montant'       => 'required|integer',
            'annee'         => 'required|date_format:Y|gte:2000',
            'sinistreId' => ['required', new HashValidatorRule(new Sinistre())],
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
            'nom.required'          => 'Le nom est obligatoire.',
            'longitude.required'    => 'La longitude est obligatoire',
            'latitude.required'     => 'La latitude est obligatoire',
            'montant'               => 'Le montant est obligatoire',
            'annee'                 => 'Veuillez préciser l\'année du sinistre',
            'sinistreId'            => 'Le sinistre est obligatoire'
        ];
    }
}
