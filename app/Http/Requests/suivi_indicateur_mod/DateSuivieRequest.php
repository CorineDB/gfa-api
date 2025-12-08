<?php

namespace App\Http\Requests\suiviIndicateur;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\Indicateur;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DateSuivieRequest extends FormRequest
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
            'annee' => 'required',
            'trimestre' => 'required|integer|min:1|max:4'
        ];
    }

}
