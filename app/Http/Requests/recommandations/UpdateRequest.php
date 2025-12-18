<?php

namespace App\Http\Requests\recommandations;

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
