<?php

namespace App\Http\Requests\table;

use App\Models\Programme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\ValidationException;

class TauxDecaissementRequest extends FormRequest
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
            'annee' => 'sometimes|required|integer',
        ];
    }

}
