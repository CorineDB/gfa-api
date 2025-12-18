<?php

namespace App\Http\Requests\indicateur;

use App\Models\Indicateur;
use App\Models\IndicateurValueKey;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;

class AddAnneesCibleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-un-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->indicateur))
        {
            $this->indicateur = Indicateur::findByKey($this->indicateur);
        }

        return [

            'anneesCible'                    => ['required', "array", "min:1"],

            'anneesCible.*.valeurCible'      => ['required', $this->indicateur->agreger ? "array" : "", $this->indicateur->agreger ? "min:".$this->indicateur->valueKeys->count() : "", $this->indicateur->agreger ? "max:".$this->indicateur->valueKeys->count() : ""],
            'anneesCible.*.valeurCible.*.keyId'            => [new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {

                // Get the index from the attribute name
                preg_match('/anneesCible\.(\d+)\.valeurCible\.(\d+)\.keyId/', $attribute, $matches);
                $index = $matches[1] ?? null; // Get the index if it exists

                // Ensure each keyId in valeurDeBase is one of the value_keys.id
                if (!in_array(request()->input('anneesCible.*.valeurCible.*.keyId')[$index], $this->indicateur->valueKeys->pluck('id')->toArray())) {
                    $fail("Le keyId n'est pas dans value_keys.");
                }

            }],
            'anneesCible.*.valeurCible.*.value'              => ['required'],

            'anneesCible.*.annee'            => ['required', 'distinct', 'date_format:Y', 'after_or_equal:anneeDeBase'],

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
