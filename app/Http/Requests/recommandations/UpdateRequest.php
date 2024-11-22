<?php

namespace App\Http\Requests\recommandations;

use App\Models\Recommandation;
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
        return request()->user()->hasRole("organisation");
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
            'recommandation' => 'sometimes'
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
