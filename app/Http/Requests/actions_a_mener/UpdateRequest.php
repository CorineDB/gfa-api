<?php

namespace App\Http\Requests\actions_a_mener;

use App\Models\EvaluationDeGouvernance;
use App\Models\Indicateur;
use App\Models\Recommandation;
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
        return request()->user()->hasRole("organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'action'            => 'sometimes',
            'start_at'          => 'sometimes|date|date_format:Y-m-d|after:today',
            'end_at'            => 'sometimes|date|date_format:Y-m-d|after:start_at',
            'evaluationId'      => ['required', new HashValidatorRule(new EvaluationDeGouvernance())]/* ,
            'recommandationId'  => ['required', new HashValidatorRule(new Recommandation())],
            'indicateurs'       => ['required', 'array', 'min:0'],
            'indicateurs.*'     => ['distinct', 'string', new HashValidatorRule(new Indicateur())] */
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
        ];
    }
}
