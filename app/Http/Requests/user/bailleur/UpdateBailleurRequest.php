<?php

namespace App\Http\Requests\user\bailleur;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBailleurRequest extends FormRequest
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
            'nom' => 'required|string|max:255',
            'contact' => 'required|string|max:12',
            'email' => 'required|email|max:255',
            'sigle' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'pays' => 'required|string|max:255'
        ];
    }
}
