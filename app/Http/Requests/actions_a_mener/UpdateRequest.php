<?php

namespace App\Http\Requests\actions_a_mener;

use App\Models\EvaluationDeGouvernance;
use App\Models\Indicateur;
use App\Models\PrincipeDeGouvernance;
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
        return request()->user()->hasPermissionTo("modifier-une-action-a-mener");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->action_a_mener))
        {
            $this->action_a_mener = EvaluationDeGouvernance::findByKey($this->action_a_mener);
        }

        return [
            'action'            => 'sometimes',
            'start_at'          => ["sometimes","date","date_format:Y-m-d",
                function ($attribute, $value, $fail) {
                    // Check if the value is different from the current start_at
                    if (isset($this->action_a_mener->start_at) && $value != $this->action_a_mener->start_at) {
                        // Apply the after_or_equal:today validation
                        if (strtotime($value) < strtotime(date('Y-m-d'))) {
                            $fail(__('The :attribute must be a date after or equal to today.', ['attribute' => $attribute]));
                        }
                    }
                }
            ],
            'end_at'            => 'sometimes|date|date_format:Y-m-d|after:start_at',
            'evaluationId'      => ['required', new HashValidatorRule(new EvaluationDeGouvernance())],
            'recommandationId'  => ['sometimes', new HashValidatorRule(new Recommandation())],

            'indicateurs'       => ['array', 'min:0'],
            'indicateurs.*'     => ['distinct', 'string', new HashValidatorRule(new Indicateur())],

            'principes_de_gouvernance'       => ['array', 'min:0'],
            'principes_de_gouvernance.*'     => ['distinct', 'string', new HashValidatorRule(new PrincipeDeGouvernance())]
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
