<?php

namespace App\Http\Requests\maitriseOeuvre;

use App\Models\Bailleur;
use App\Models\MaitriseOeuvre;
use App\Rules\HashValidatorRule;
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

        return $user->hasRole("unitee-de-gestion", "mod");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->maitrise_oeuvre))
        {
            $this->maitrise_oeuvre = MaitriseOeuvre::findByKey($this->categorie);
        }

        return [
            'nom' => ['required','max:255', Rule::unique('maitrise_oeuvres')->ignore($this->categorie)->whereNull('deleted_at')],
            'reference' => 'required|max:255',
            'estimation' => 'required|integer',
            'engagement' => 'required|integer',
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
            'nom.unique'                => 'Le nom de la maitrise d\'oeuvre doit être unique.',
            'estimation.required'       => 'L\'estimation est obligatoire.',
            'engagement.required'       => 'Le montant de l\'engagement est obligatoire.',
            'reference.required'       => 'La référence est obligatoire.',
            'bailleurId.required'       => 'Veuillez préciser le bailleur.',
            'bailleurId.exists'         => 'Bailleur inconnu. Veuillez sélectionner un bailleur dans le système',
        ];
    }
}
