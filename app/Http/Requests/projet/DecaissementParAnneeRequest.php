<?php

namespace App\Http\Requests\projet;

use Illuminate\Foundation\Http\FormRequest;

class DecaissementParAnneeRequest extends FormRequest
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
            'annee' => 'required|integer|min:2000'
        ];
    }
}
