<?php

namespace App\Http\Requests\indicateur;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\IndicateurValueKey;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\Unitee;
use App\Models\UniteeDeGestion;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
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
        return request()->user()->hasPermissionTo("creer-un-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $programme = auth()->user()->programme;

        return [
            'nom'                           => ['required', 'string', Rule::unique('indicateurs', 'nom')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'sources_de_donnee'             => 'nullable',
            'frequence_de_la_collecte'      => 'nullable',
            'methode_de_la_collecte'        => 'nullable',
            'responsables'                  => ['nullable', 'array'],
            'responsables.ug'               => [Rule::requiredIf(!request()->input('responsables.organisations')), !empty(request()->input('responsables.organisations')) ? 'nullable' :'', 'string', new HashValidatorRule(new UniteeDeGestion())],
            'responsables.organisations'    => [Rule::requiredIf(empty(request()->input('responsables.ug')) === true), 'array', 'min:0'],
            'responsables.organisations.*'  => ['distinct', 'string', new HashValidatorRule(new Organisation())],

            'anneeDeBase'                   => ['nullable', 'date_format:Y', 'after_or_equal:'.Carbon::parse($programme->debut)->year, 'before_or_equal:'.Carbon::parse($programme->fin)->year, 'before_or_equal:'.now()->format("Y")],

            "type_de_variable"              => ["sometimes", "in:quantitatif,qualitatif,dichotomique"],

            "agreger"                       => ["required", "boolean:false"],

            'uniteeMesureId'                => ['nullable', Rule::requiredIf(!empty(request()->input('valeurDeBase')) || count(request()->input('anneesCible'))>0), new HashValidatorRule(new Unitee())],

            "indice"                        => ["required", "integer", "min:0"],
            'categorieId'                   => ['required', new HashValidatorRule(new Categorie())],
            
            'value_keys'                    => [Rule::requiredIf(request()->input('agreger')), request()->input('agreger') ? "array" : "", function($attribute, $value, $fail){
                if(!request()->input('agreger') && (is_array(request()->input('value_keys')))){
                    $fail("Champ non requis.");
                }
            }, request()->input('agreger') ? "min:1" : ""],
            'value_keys.*.id'               => [Rule::requiredIf(request()->input('agreger')), "string", 'distinct', new HashValidatorRule(new IndicateurValueKey())],
            'value_keys.*.uniteeMesureId'   => ["nullable", "string", new HashValidatorRule(new Unitee())],


            'valeurDeBase'                  => ['nullable', request()->input('agreger') ? "array" : "", function($attribute, $value, $fail){
                if(!request()->input('agreger') && (is_array(request()->input('valeurDeBase')))){
                    $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                }
                
            }, request()->input('agreger') ? "min:".count(request()->input('value_keys')) : "", request()->input('agreger') ? "max:".count(request()->input('value_keys')) : ""],
            'valeurDeBase.*.keyId'            => ['distinct', new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {

                // Get the index from the attribute name
                preg_match('/valeurDeBase\.(\d+)\.keyId/', $attribute, $matches);
                $index = $matches[1] ?? null; // Get the index if it exists
                
                // Ensure each keyId in valeurDeBase is one of the value_keys.id
                if (!in_array(request()->input('valeurDeBase.*.keyId')[$index], collect(request()->input('value_keys.*.id'))->toArray())) {
                    $fail("Le keyId n'est pas dans value_keys.");
                }
    
            }],
            'valeurDeBase.*.value'              => ['required'],

            'anneesCible'                    => ['nullable', "array", request()->input('anneesCible') ? "min:1" : ""],

            'anneesCible.*.valeurCible'      => ['required', request()->input('agreger') ? "array" : "", function($attribute, $value, $fail){
                if(!request()->input('agreger') && (is_array(request()->input('valeurDeBase')))){
                    $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                }
                
            }, request()->input('agreger') ? "min:".count(request()->input('value_keys')) : "", request()->input('agreger') ? "max:".count(request()->input('value_keys')) : ""],
            'anneesCible.*.valeurCible.*.keyId'            => [new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {

                // Get the index from the attribute name
                preg_match('/anneesCible\.(\d+)\.valeurCible\.(\d+)\.keyId/', $attribute, $matches);
                $index = $matches[1] ?? null; // Get the index if it exists
                
                // Ensure each keyId in valeurDeBase is one of the value_keys.id
                if (!in_array(request()->input('anneesCible.*.valeurCible.*.keyId')[$index], collect(request()->input('value_keys.*.id'))->toArray())) {
                    $fail("Le keyId n'est pas dans value_keys.");
                }
    
            }],
            'anneesCible.*.valeurCible.*.value'              => ['required'],

            'anneesCible.*.annee'            => ['required', 'distinct', 'date_format:Y', 'after_or_equal:anneeDeBase'],

            'sites'                         => ['nullable', "array", request()->input('sites') ? "min:1" : ""],
            'sites.*'                       => ['distinct', new HashValidatorRule(new Site())],
            
            //'bailleurId'    => [Rule::requiredIf(request()->user()->hasRole(['unitee-de-gestion'])), new HashValidatorRule(new Bailleur())]
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
