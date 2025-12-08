<?php

namespace App\Http\Requests\maitriseOeuvre;

use App\Models\Bailleur;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::user();

        return $user->hasRole("unitee-de-gestion", "mod");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => 'required|max:255|unique:maitrise_oeuvres,nom',
            'reference' => 'required|max:255',
            'estimation' => 'required|integer',
            'engagement' => 'required|integer',
            'attributaire' => 'required|array',
            'attrbutaire.*' => 'required',
            'bailleurId' => ['required', new HashValidatorRule(new Bailleur())],
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
            'nom.required'              => 'Le nom  est obligatoire.',
            'estimation.required'       => 'L\'estimation est obligatoire.',
            'engagement.required'       => 'Le montant de l\'engagement est obligatoire.',
            'reference.required'       => 'La référence est obligatoire.',
            'bailleurId.required'       => 'Veuillez préciser le bailleur.',
            'bailleurId.exists'         => 'Bailleur inconnu. Veuillez sélectionner un bailleur dans le système',
        ];
    }
}
