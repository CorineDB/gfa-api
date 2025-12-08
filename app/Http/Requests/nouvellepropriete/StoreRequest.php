<?php

namespace App\Http\Requests\nouvellepropriete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Propriete;

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
            'nom' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'montant' => 'required|integer',
            'annee' => 'required|integer',
            'proprieteId'   => ['required', Rule::exists('proprietes', 'id')->whereNull('deleted_at')],
        ];
    }

    protected function prepareForValidation(): void
    {

        $propriete = Propriete::decodeKey($this->proprieteId);

        if(!$propriete)
            throw ValidationException::withMessages(['proprieteId' => "Sinistre inconnue"]);

        $this->merge([
            'proprieteId' => $propriete
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nom.required'       => 'Le nom est obligatoire.',
            'longitude.required' => 'La longitude est obligatoire',
            'latitude.required' => 'La latitude est obligatoire',
            'montant' => 'Le montant est obligatoire',
            'proprieteId' => 'Le propriete est obligatoire'
        ];
    }
}
