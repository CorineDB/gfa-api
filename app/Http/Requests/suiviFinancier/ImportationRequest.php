<?php

namespace App\Http\Requests\suiviFinancier;

use Illuminate\Foundation\Http\FormRequest;

class ImportationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("importer-un-suivi-financier") || request()->user()->hasRole("unitee-de-gestion", "organisation");

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
            'fichier' => 'required'
        ];

    }

}
