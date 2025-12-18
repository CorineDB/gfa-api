<?php

namespace App\Http\Requests\agence;

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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->agences_de_communication))
        {
            $this->agences_de_communication = OngCom::findByKey($this->agences_de_communication);
        }

        return [
            "nom"       => ['required','max:255', Rule::unique('users')->ignore($this->agence_de_communication->user)->whereNull('deleted_at')],
            "contact"   => ['required','max:255', Rule::unique('users')->ignore($this->agence_de_communication->user)->whereNull('deleted_at')],
            "email"     => ['required','max:255', Rule::unique('users')->ignore($this->agence_de_communication->user)->whereNull('deleted_at')]
        ];
    }
}
