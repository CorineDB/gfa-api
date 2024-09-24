<?php

namespace App\Http\Requests\paye;

use App\Models\Paye;
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
            'montant' => 'required|integer|size:'. $this->max,
            'proprieteId'   => ['required', Rule::exists('proprietes', 'id')->whereNull('deleted_at')],
        ];
    }

    protected function prepareForValidation(): void
    {

        $propriete = Propriete::decodeKey($this->proprieteId);

        if(!$propriete)
            throw ValidationException::withMessages(['proprieteId' => "Propriete inconnue"]);

        $this->merge([
            'proprieteId' => $propriete
        ]);

        $propriete = Propriete::find($propriete);

        $this->merge([
            'max' => $propriete->montant
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
            'montant' => 'Le montant est obligatoire',
            'proprieteId' => 'La proprieté est obligatoire'
        ];
    }
}
