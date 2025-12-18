<?php

namespace App\Http\Requests\check_list_com;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\Unitee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::user();

        return $user->hasRole("ong", "agence");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'nom'           => 'required',

            'code'          => 'required|max:255',

            'uniteId'      => ['required', Rule::exists('unitees', 'id')->whereNull('deleted_at')],

        ];
    }

    protected function prepareForValidation(): void
    {

        $uniteeMesure = Unitee::decodeKey($this->uniteId);

        if(!$uniteeMesure)
            throw ValidationException::withMessages(['uniteId' => "Unitee de mésure inconnue"]);


        $this->merge([
            'uniteId' => $uniteeMesure
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nom.required'          => 'Veuillez préciser le nom de la check list.',
            'code.required'         => 'Veuillez préciser le code de la check list',
            'uniteId.required'     => 'Veuillez préciser l\'unitée de mésure.',
            'uniteId.exists'       => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système'
        ];
    }
}
