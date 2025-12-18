<?php

namespace App\Http\Requests\enquetes_de_gouvernance\recommandations;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
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
        return request()->user()->hasPermissionTo("modifier-une-recommandation") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->recommandation))
        {
            $this->recommandation = Recommandation::findByKey($this->recommandation);
        }

        return [
            'recommandation' => 'sometimes',
            'evaluationId'      => ['sometimes', new HashValidatorRule(new EvaluationDeGouvernance())],
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
