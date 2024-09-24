<?php

namespace App\Http\Requests\duree;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDureeRequest extends FormRequest
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
            'debut' => 'required|date|date_format:Y-m-d',
            'fin' => 'required|date|date_format:Y-m-d|after_or_equal:debut',
            'activiteId' => 'sometime|required',
            'tacheId' => 'sometime|required'
        ];
    }
}
