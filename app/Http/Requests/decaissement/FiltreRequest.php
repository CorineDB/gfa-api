<?php

namespace App\Http\Requests\decaissement;

use App\Models\Bailleur;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class FiltreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("voir-un-decaissement") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'projetId'   => ['required', new HashValidatorRule(new Projet())],

            'type'    => 'required',

            'debut'    => 'sometimes|required|date|date_format:Y-m-d',

            'fin'    => 'sometimes|required|date|date_format:Y-m-d',

        ];
    }
}
