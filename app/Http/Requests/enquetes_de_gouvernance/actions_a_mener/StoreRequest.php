<?php

namespace App\Http\Requests\enquetes_de_gouvernance\actions_a_mener;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
use App\Models\enquetes_de_gouvernance\Recommandation;
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
        return request()->user()->hasPermissionTo("creer-une-action-a-mener");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'action'            => 'required',
            'start_at'          => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'end_at'            => 'required|date|date_format:Y-m-d|after:start_at',
            'evaluationId'      => ['required', new HashValidatorRule(new EvaluationDeGouvernance())],
            'recommandationId'  => ['sometimes', new HashValidatorRule(new Recommandation())],

            'indicateurs'       => ['array', 'min:0'],
            'indicateurs.*'     => ['distinct', 'string', new HashValidatorRule(new IndicateurDeGouvernanceFactuel())],
            'questions_operationnelle'       => ['array', 'min:0'],
            'questions_operationnelle.*'     => ['distinct', 'string', new HashValidatorRule(new QuestionOperationnelle())],

            'principes_factuel_de_gouvernance'       => ['array', 'min:0'],
            'principes_factuel_de_gouvernance.*'     => ['distinct', 'string', new HashValidatorRule(new PrincipeDeGouvernanceFactuel())],

            'principes_de_perception_de_gouvernance'       => ['array', 'min:0'],
            'principes_de_perception_de_gouvernance.*'     => ['distinct', 'string', new HashValidatorRule(new PrincipeDeGouvernancePerception())]
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
