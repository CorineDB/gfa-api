<?php

namespace App\Http\Requests\activite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class  FiltreActiviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("voir-une-activite") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'annee' => 'required|integer'
        ];
    }

    // protected function prepareForValidation(): void
    // {
    //     if($this->toPermute == 0)
    //     {
    //         if(!isset($this->position)) throw ValidationException::withMessages(['position' => "La nouvelle position est obligatoire"]);
    //     }
    // }
}
