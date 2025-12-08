<?php

namespace App\Http\Requests\activite;

use App\Models\Composante;
use App\Models\Programme;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PpmRequest extends FormRequest
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
            'composanteId'      => ['sometimes|required', new HashValidatorRule(new Composante())],
            'projetId'      => ['sometimes|required', new HashValidatorRule(new Projet())],
            'programmeId'      => ['sometimes|required', new HashValidatorRule(new Programme())],
        ];
    }

}
