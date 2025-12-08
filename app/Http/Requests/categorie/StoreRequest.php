<?php

namespace App\Http\Requests\categorie;

use App\Models\Categorie;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-une-categorie") || request()->user()->hasRole("unitee-de-gestion");
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'           => ['required', 'string', Rule::unique('categories', 'nom')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],
            "type"          => ["required", "in:impact,effet,produit"],
            "indice"        => ["required", "integer", "min:0"],
            'categorieId'   => ['sometimes', 'nullable', new HashValidatorRule(new Categorie())],
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
