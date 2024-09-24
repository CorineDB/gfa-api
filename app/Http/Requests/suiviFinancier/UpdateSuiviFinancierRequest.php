<?php

namespace App\Http\Requests\suiviFinancier;

use App\Models\Activite;
use App\Rules\HashValidatorRule;
use App\Rules\YearValidationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuiviFinancierRequest extends FormRequest
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
            'activiteId' => ['sometimes|required', new HashValidatorRule(new Activite())],
            'consommer' => 'sometimes|required|integer',
            'dateDeSuivie'    => [Rule::requiredIf(!request('trimestre')), 'date_format:Y-m-d', new YearValidationRule, function(){
                $this->merge([
                    "trimestre" => Carbon::parse(request('dateDeSuivie'))->quarter,
                    "annee" => Carbon::parse(request('dateDeSuivie'))->format('Y')
                ]);
            }],
            'trimestre' => 'sometimes|required|integer|min:1|max:4',
            'commentaire'          => 'sometimes',
            'type' => 'sometimes|required|integer|min:0|max:1'
        ];

    }

}
