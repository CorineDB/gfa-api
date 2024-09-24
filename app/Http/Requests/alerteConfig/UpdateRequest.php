<?php

namespace App\Http\Requests\alerteConfig;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'nombreDeJourAvant' => 'sometimes|required|integer|min:1',
            'frequence' => 'sometimes|required|integer|min:1',
            'frequenceRapport' => 'sometimes|required|integer|min:1',
            'debutSuivi' => 'sometimes|required|date',
            'frequenceBackup' => 'sometimes|required|in:everyMinute,
                                                        everyTwoMinutes,
                                                        everyThreeMinutes,
                                                        everyFourMinutes,
                                                        everyFiveMinutes,
                                                        everyTenMinutes,
                                                        everyFifteenMinutes,
                                                        everyThirtyMinutes,
                                                        hourly,
                                                        everyTwoHours,
                                                        everyThreeHours,
                                                        everyFourHours,
                                                        everySixHours,
                                                        daily,
                                                        weekly,
                                                        monthly,
                                                        quarterly,
                                                        yearly'
        ];
    }
}
