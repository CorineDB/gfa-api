<?php

namespace App\Http\Requests\composante;

use App\Models\Composante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeplacerRequest extends FormRequest
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
            'composanteId' => ['required', Rule::exists('composantes', 'id')->whereNull('deleted_at')],
            'topermute' => 'required|integer|min:0|max:1',
        ];
    }

    protected function prepareForValidation(): void
    {

        $composante = Composante::decodeKey($this->composanteId);

        if(!$composante)
            throw ValidationException::withMessages(['composanteId' => "Composante inconnue"]);


        $this->merge([
            'composanteId' => $composante
        ]);

        if($this->toPermute == 0)
        {
            if(!isset($this->position)) throw ValidationException::withMessages(['position' => "La nouvelle position est obligatoire"]);
        }

    }
}
