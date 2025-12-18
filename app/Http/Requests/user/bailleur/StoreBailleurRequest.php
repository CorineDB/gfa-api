<?php

namespace App\Http\Requests\user\bailleur;

use Illuminate\Foundation\Http\FormRequest;

class StoreBailleurRequest extends FormRequest
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
            'contact' => 'required|string|max:12|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'sigle' => 'required|string|max:255|unique:users',
            'code' => 'required|string|max:255|unique:users',
            'pays' => 'required|string|max:255'
        ];
    }
}
