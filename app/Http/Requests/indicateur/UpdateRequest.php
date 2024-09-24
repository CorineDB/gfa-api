<?php

namespace App\Http\Requests\indicateur;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\Unitee;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;
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
            'nom'           => 'sometimes|required',

            'anneeDeBase'   => 'sometimes|required|date_format:Y|before_or_equal:now()->format(Y).',

            'valeurDeBase'  => 'sometimes|required',

            'uniteeMesureId'   => ['sometimes', 'required', new HashValidatorRule(new Unitee())],

            //'unitees_mesure'=> 'required|array',

            'categorieId'   => ['sometimes', 'required', new HashValidatorRule(new Categorie())],

            'bailleurId'    => ['sometimes', 'required', Rule::requiredIf(request()->user()->hasRole(['unitee-de-gestion'])),  new HashValidatorRule(new Bailleur())]
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
            'nom.required'          => 'Le nom de l\'indicateur est obligatoire.',
            'description.required'  => 'La description de l\'indicateur est obligatoire.',
            'anneeDeBase.required'  => 'L\'annee de base est obligatoire.',
            'valeurDeBase.required' => 'La valeur de base est obligatoire.',
            'unites.required'       => 'Veuillez préciser les unites de mésure de l\'indicateur',
            'unites.array'          => 'Veuillez préciser les unites de mesure de l\'indicateur dans un tableau',
            'categorieId.exists'    => 'Catégorie inconnu. Veuillez sélectionner une catégorie existant dans le système',
            'bailleurId.required'   => 'Veuillez préciser le bailleur.',
            'bailleurId.exists'     => 'Bailleur inconnu. Veuillez sélectionner un bailleur existant dans le système',
            'uniteeMesureId.required'   => 'Veuillez préciser l\'unitée de mésure.',
            'uniteeMesureId.exists'     => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système',
        ];
    }
}
