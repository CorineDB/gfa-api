<?php

namespace App\Http\Requests\ong;

use App\Models\OngCom;
use App\Models\User;
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
        return request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->ong))
        {
            $this->ong = OngCom::findByKey($this->ong);
        }

        return [
            "nom"       => ['required','max:255', Rule::unique('users')->ignore($this->ong->user)->whereNull('deleted_at')],
            "contact"   => ['required','max:255', Rule::unique('users')->ignore($this->ong->user)->whereNull('deleted_at')],
            "email"     => ['required','max:255', Rule::unique('users')->ignore($this->ong->user)->whereNull('deleted_at')]
        ];
    }
}
