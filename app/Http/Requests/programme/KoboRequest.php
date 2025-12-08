<?php

namespace App\Http\Requests\programme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KoboRequest extends FormRequest
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
            'url' => 'required'
        ];
    }

     /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */

}
