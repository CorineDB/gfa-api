<?php

namespace App\Http\Requests\formulaire;

use App\Models\Formulaire;
use App\Models\User;
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
            'nom' => 'required|unique:formulaires,nom',
            'type' => 'required|integer|min:0|max:1',
            'json' =>'required|array'
        ];
    }
}
