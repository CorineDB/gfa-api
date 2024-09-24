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
            'nom'           => 'required',

            'anneeDeBase'   => 'required|date_format:Y|before_or_equal:'.now()->format("Y"),

            'valeurDeBase'  => 'required',

            'uniteeMesureId'   => ['required', new HashValidatorRule(new Unitee())],

            'categorieId'   => ['nullable', new HashValidatorRule(new Categorie())],

            'bailleurId'    => [Rule::requiredIf(request()->user()->hasRole(['unitee-de-gestion'])), new HashValidatorRule(new Bailleur())]

        ];
    }

    /*

        protected function prepareForValidation(): void
        {

            if(isset($this->categorieId))
            {
                $categorie = Categorie::decodeKey($this->categorieId);

                if(!$categorie)
                    throw ValidationException::withMessages(['categorieId' => "Catégorie inconnue"]);

                $this->merge([
                    'categorieId' => $categorie
                ]);
            }

            if(isset($this->bailleurId))
            {
                $bailleur = Bailleur::decodeKey($this->bailleurId);

                if(!$bailleur)
                    throw ValidationException::withMessages(['bailleurId' => "Bailleur inconnue"]);


                $this->merge([
                    'bailleurId' => $bailleur
                ]);
            }

            $uniteeMesure = Unitee::decodeKey($this->uniteeMesureId);

            if(!$uniteeMesure)
                throw ValidationException::withMessages(['uniteeMesureId' => "Unitee de mésure inconnue"]);


            $this->merge([
                'uniteeMesureId' => $uniteeMesure
            ]);
        }

    */

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
            'uniteeMesureId.exists'     => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système'
        ];
    }
}
