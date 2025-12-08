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
        return request()->user()->hasPermissionTo("modifier-une-categorie") || request()->user()->hasRole("unitee-de-gestion");
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
            'nom'           => ['sometimes', 'string', Rule::unique('categories', 'nom')->ignore($this->categorie)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            "type"          => ["sometimes", "in:impact,effet,produit"],
            "indice"        => ["sometimes", "integer", "min:0"],
            'categorieId'   => ['sometimes', 'nullable', new HashValidatorRule(new Categorie())]
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
