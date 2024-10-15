<?php

namespace App\Http\Requests\categorie;

use App\Models\Categorie;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        if(is_string($this->categorie))
        {
            $this->categorie = Categorie::findByKey($this->categorie);
        }

        return [
            'nom'  => ['sometimes','max:255', Rule::unique('categories','nom')->ignore($this->categorie)->whereNull('deleted_at')],
            'categorieId' => ['sometimes','nullable', new HashValidatorRule(new Categorie())],
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
        ];
    }
}
