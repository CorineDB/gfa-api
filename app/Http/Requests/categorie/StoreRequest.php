<?php

namespace App\Http\Requests\categorie;

use App\Models\Categorie;
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
            'nom' => 'required|max:255|unique:categories,nom',
            'categorieId' => ['nullable', new HashValidatorRule(new Categorie())],
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
            'nom.required' => 'Le nom de la categorie est obligatoire.',
            'nom.unique' => 'Catégorie déjà enrégistré.'
        ];
    }
}
