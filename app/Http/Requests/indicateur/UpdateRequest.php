<?php

namespace App\Http\Requests\indicateur;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\Indicateur;
use App\Models\IndicateurValueKey;
use App\Models\Site;
use App\Models\Unitee;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;
use Carbon\Carbon;
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
        if(is_string($this->indicateur))
        {
            $this->indicateur = Indicateur::findByKey($this->indicateur);
        }

        $programme = auth()->user()->programme;

        $nbreKeys = $this->indicateur->valueKeys->count() ?? 1;

        return [
            'nom'                       => ['sometimes', 'max:255', Rule::unique('indicateurs', 'nom')->ignore($this->indicateur)->whereNull('deleted_at')],
            'sources_de_donnee'             => 'sometimes',
            'frequence_de_la_collecte'      => 'sometimes',
            'methode_de_la_collecte'        => 'sometimes',
            'responsable'                   => 'sometimes',
            'anneeDeBase'                   => ['sometimes', 'date_format:Y', 'after_or_equal:'.Carbon::parse($programme->debut)->year, 'before_or_equal:'.Carbon::parse($programme->fin)->year, 'before_or_equal:'.now()->format("Y")],

            "type_de_variable"              => ["sometimes", "in:quantitatif,qualitatif,dichotomique"],

            "agreger"                       => ["sometimes", "boolean:false", function($attribute, $value, $fail){

                if(request()->input($attribute) != null && request()->input('agreger') != $this->indicateur->agreger && $this->indicateur->suivis->isNotEmpty()) {
                    $fail('Cet indicateur a deja ete suivi et donc ne peut plus etre mis a jour.');
                }
            }],

            'uniteeMesureId'                => ['sometimes', Rule::requiredIf(!request()->input('agreger')), new HashValidatorRule(new Unitee())],

            'categorieId'                   => ['nullable', new HashValidatorRule(new Categorie())],
            'sites'                         => ['required', 'array', 'min:1'],
            'sites.*'                         => ['distinct', new HashValidatorRule(new Site())],

            'valeurDeBase'                  => ['sometimes', (request()->input('agreger') != null && request()->input('agreger')) ? "array" : "", function($attribute, $value, $fail){
                    if(!request()->input('agreger') && is_array(request()->input('valeurDeBase'))){
                        $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                    }

                    /*if(!(request()->input('agreger') && $this->indicateur->agreger) && (is_array(request()->input('valeurDeBase')))){
                        $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                    }*/
                    
                }, (request()->input('agreger') != null && request()->input('agreger') == $this->indicateur->agreger) ? (request()->input('agreger') ? "min:".$nbreKeys : ($this->indicateur->agreger ? "min:".$nbreKeys : "")) : (request()->input('agreger') ? "min:1":""), (request()->input('agreger') != null && request()->input('agreger') == $this->indicateur->agreger) ? (request()->input('agreger') ? "max:".$nbreKeys : ($this->indicateur->agreger ? "max:".$nbreKeys : "")) : ""//request()->input('agreger') ? "min:".$nbreKeys : ($this->indicateur->agreger ? "min:".$nbreKeys : ""), request()->input('agreger') ? "max:".$nbreKeys : ($this->indicateur->agreger ? "max:".$nbreKeys : "")
            ],

            'valeurDeBase.*.keyId'            => ['distinct', new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {

                if (request()->input('agreger') != null && request()->input('agreger') != $this->indicateur->agreger && !($this->indicateur->valueKeys()->where('indicateurValueKeyId', request()->input($attribute))->exists())) {
                    $fail('The selected keyId is not for the given Indicateur.');
                }
            }],
            'valeurDeBase.*.value'          => ['required'],
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
