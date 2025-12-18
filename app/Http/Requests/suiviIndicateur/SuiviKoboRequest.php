<?php

namespace App\Http\Requests\suiviIndicateur;

use App\Models\Indicateur;
use App\Models\ValeurCibleIndicateur;
use App\Rules\HashValidatorRule;
use App\Rules\YearValidationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SuiviKoboRequest extends FormRequest
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
            '__version__' => 'required',

            '_submission_time'    => [function(){
                $this->merge([
                    "dateSuivie" => Carbon::parse(request('_submission_time'))->format("Y-m-d H:i:s")
                ]);

                $this->merge([
                    "trimestre" => Carbon::parse(request('dateSuivie'))->quarter,
                    "annee" => Carbon::parse(request('dateSuivie'))->format("Y")
                ]);


            }],
        ];
    }
}
