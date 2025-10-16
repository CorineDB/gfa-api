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
        return request()->user()->hasPermissionTo("creer-un-suivi-financier") || request()->user()->hasRole("unitee-de-gestion", "organisation");

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
            'consommer' => 'required|integer|min:0|max:9999999999999',
            'dateDeSuivi'    => [Rule::requiredIf(!request('trimestre')), 'date_format:Y-m-d', new YearValidationRule, function(){
                $this->merge([
                    "trimestre" => Carbon::parse(request('dateDeSuivi'))->quarter,
                    "annee" => Carbon::parse(request('dateDeSuivi'))->format('Y')
                ]);
            }],

            'annee'         => [Rule::requiredIf(!request('dateDeSuivi')), "integer", "digits:4", "date_format:Y", /* 'between:1900,' . now()->year, */ "gte:1940"],
            'trimestre'     =>  [Rule::requiredIf(!request('dateDeSuivi')), "integer", "min:1", "max:4"],

            'type' => 'sometimes|integer|in:fond-propre,budget-alloue',
            'commentaire'          => 'sometimes',
        ];
    }

}
