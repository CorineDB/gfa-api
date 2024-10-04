<?php

namespace App\Http\Requests\suiviFinancier;

use App\Models\Activite;
use App\Rules\HashValidatorRule;
use App\Rules\YearValidationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuiviFinancierRequest extends FormRequest
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
            'activiteId' => ['required', new HashValidatorRule(new Activite())],
            'consommer' => 'required|integer',
            'dateDeSuivie'    => [Rule::requiredIf(!request('trimestre')), 'date_format:Y-m-d', new YearValidationRule, function(){
                $this->merge([
                    "trimestre" => Carbon::parse(request('dateDeSuivie'))->quarter,
                    "annee" => Carbon::parse(request('dateDeSuivie'))->format('Y')
                ]);
            }],
            'annee' => ["required", "integer", "digits:4", /*'between:1900,' . now()->year*/], // Validates year between 1900 and the current year
            'trimestre' => 'integer|min:1|max:4',
            'type' => 'sometimes|integer|min:0|max:1',
            'commentaire'          => 'sometimes',
        ];
    }

}
