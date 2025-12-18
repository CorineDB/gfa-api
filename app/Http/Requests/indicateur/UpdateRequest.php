<?php

namespace App\Http\Requests\indicateur;

use App\Models\Bailleur;
use App\Models\Categorie;
use App\Models\Indicateur;
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
        return request()->user()->hasPermissionTo("modifier-un-indicateur") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (is_string($this->indicateur)) {
            $this->indicateur = Indicateur::findByKey($this->indicateur);
        }

        $programme = auth()->user()->programme;

        $nbreKeys = $this->indicateur->valueKeys->count() ?? 1;

        return [
            'nom'                           => ['sometimes', 'string', Rule::unique('indicateurs', 'nom')->ignore($this->indicateur)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'sources_de_donnee'             => 'nullable',
            'frequence_de_la_collecte'      => 'nullable',
            'methode_de_la_collecte'        => 'nullable',
            'hypothese'                     => 'nullable',

            'responsables'                  => ['sometimes', 'array'],
            'responsables.ug'               => ['sometimes', Rule::requiredIf(!request()->input('responsables.organisations')), !empty(request()->input('responsables.organisations')) ? 'nullable' : '', 'string', new HashValidatorRule(new UniteeDeGestion())],
            'responsables.organisations'    => ['sometimes', Rule::requiredIf(empty(request()->input('responsables.ug')) === true), 'array', 'min:0'],

            'responsables.organisations.*'  => ['distinct', 'string', new HashValidatorRule(new Organisation())],

            'anneeDeBase'                   => ['nullable', 'date_format:Y', 'after_or_equal:' . Carbon::parse($programme->debut)->year, 'before_or_equal:' . Carbon::parse($programme->fin)->year, 'before_or_equal:' . now()->format("Y")],

            "type_de_variable"              => ["sometimes", "in:quantitatif,qualitatif,dichotomique"],

            "agreger"                       => ["sometimes", "boolean:false", function ($attribute, $value, $fail) {

                if (request()->input($attribute) != null && request()->input('agreger') != $this->indicateur->agreger && $this->indicateur->suivis->isNotEmpty()) {
                    $fail('Cet indicateur a deja ete suivi et donc ne peut plus etre mis a jour.');
                }
            }],

            'uniteeMesureId'                => ['sometimes', Rule::requiredIf(!request()->input('agreger') || request()->input('valeurDeBase') || request()->input('anneesCible')), new HashValidatorRule(new Unitee())],

            "indice"                        => ["sometimes", "integer", "min:0"],
            'categorieId'                   => ['sometimes', new HashValidatorRule(new Categorie())],
            'sites'                         => ['sometimes', 'array', 'min:1'],
            'sites.*'                         => ['distinct', new HashValidatorRule(new Site())],

            'value_keys'                    => ['sometimes', Rule::requiredIf(request()->input('agreger')), request()->input('agreger') ? "array" : "", function ($attribute, $value, $fail) {
                if (!request()->input('agreger') && (is_array(request()->input('value_keys')))) {
                    $fail("Champ non requis.");
                }
            }, request()->input('agreger') ? "min:1" : ""],
            'value_keys.*.id'               => [Rule::requiredIf(request()->input('agreger')), "string", 'distinct', new HashValidatorRule(new IndicateurValueKey())],
            'value_keys.*.uniteeMesureId'   => ["nullable", "string", new HashValidatorRule(new Unitee())],

            'valeurDeBase'                  => [
                'sometimes',
                (request()->input('agreger') != null && request()->input('agreger')) ? "array" : "",
                function ($attribute, $value, $fail) {
                    if (!request()->input('agreger') && is_array(request()->input('valeurDeBase'))) {
                        $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                    }

                    /*if(!(request()->input('agreger') && $this->indicateur->agreger) && (is_array(request()->input('valeurDeBase')))){
                        $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                    }*/
                },
                (request()->input('agreger') != null && request()->input('agreger') == $this->indicateur->agreger) ? (request()->input('agreger') ? "min:" . $nbreKeys : ($this->indicateur->agreger ? "min:" . $nbreKeys : "")) : (request()->input('agreger') ? "min:1" : ""),
                (request()->input('agreger') != null && request()->input('agreger') == $this->indicateur->agreger) ? (request()->input('agreger') ? "max:" . $nbreKeys : ($this->indicateur->agreger ? "max:" . $nbreKeys : "")) : "" //request()->input('agreger') ? "min:".$nbreKeys : ($this->indicateur->agreger ? "min:".$nbreKeys : ""), request()->input('agreger') ? "max:".$nbreKeys : ($this->indicateur->agreger ? "max:".$nbreKeys : "")
            ],

            'valeurDeBase.*.keyId'            => ['distinct', new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {

                if (request()->input('agreger') != null && request()->input('agreger') != $this->indicateur->agreger && !($this->indicateur->valueKeys()->where('indicateurValueKeyId', request()->input($attribute))->exists())) {
                    $fail('The selected keyId is not for the given Indicateur.');
                }
            }],
            'valeurDeBase.*.value'          => ['required'],

            'anneesCible'                    => ['sometimes', "array", request()->input('anneesCible') ? "min:1" : ""],

            'anneesCible.*.valeurCible'      => ['required', request()->input('agreger') ? "array" : "", function ($attribute, $value, $fail) {
                if (!request()->input('agreger') && (is_array(request()->input('valeurDeBase')))) {
                    $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                }
            }, request()->input('agreger') ? "min:" . count(request()->input('value_keys')) : "", request()->input('agreger') ? "max:" . count(request()->input('value_keys')) : ""],
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
            'sites'                         => ['sometimes', 'array', 'min:1'],
            'sites.*'                       => ['distinct', new HashValidatorRule(new Site())],

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
            'nom.required'                    => 'Merci de renseigner le nom de l’indicateur.',
            'nom.unique'                      => 'Un indicateur avec ce nom existe déjà dans votre programme.',
            'description.required'            => 'Merci de fournir une description pour l’indicateur.',
            'anneeDeBase.required'            => 'Veuillez indiquer l’année de base pour l’indicateur.',
            'anneeDeBase.date_format'         => 'L’année de base doit être au format YYYY.',
            'anneeDeBase.after_or_equal'      => 'L’année de base ne peut pas être antérieure au début du programme.',
            'anneeDeBase.before_or_equal'     => 'L’année de base ne peut pas dépasser l’année en cours ou la fin du programme.',

            'uniteeMesureId.required'         => 'Veuillez sélectionner l’unité de mesure de l’indicateur.',
            'uniteeMesureId.exists'           => 'L’unité de mesure sélectionnée est invalide.',

            'categorieId.required'            => 'Veuillez sélectionner une catégorie pour l’indicateur.',
            'categorieId.exists'              => 'La catégorie sélectionnée n’existe pas dans le système.',

            'responsables.ug.required'        => 'Veuillez sélectionner une unité de gestion responsable.',
            'responsables.organisations.required' => 'Veuillez sélectionner au moins une organisation responsable.',
            'responsables.organisations.*.distinct' => 'Chaque organisation responsable doit être unique.',
            'responsables.organisations.*.exists' => 'L’organisation sélectionnée est invalide.',

            'agreger.boolean'                  => 'Le champ “agréger” doit être vrai ou faux.',
            'agreger.*'                        => 'Cet indicateur a déjà été suivi et ne peut plus être modifié pour l’agrégation.',

            'value_keys.required'              => 'Veuillez fournir au moins une clé de valeur pour cet indicateur agrégé.',
            'value_keys.array'                 => 'Les clés de valeur doivent être envoyées sous forme de liste.',
            'value_keys.min'                   => 'Au moins une clé de valeur est nécessaire.',
            'value_keys.*.id.required'         => 'Chaque clé de valeur doit avoir un identifiant.',
            'value_keys.*.id.distinct'         => 'Chaque clé de valeur doit être unique.',
            'value_keys.*.uniteeMesureId.exists' => 'L’unité de mesure pour la clé de valeur est invalide.',

            'valeurDeBase.required'            => 'Veuillez renseigner la valeur de base pour cet indicateur.',
            'valeurDeBase.array'               => 'La valeur de base doit être fournie sous forme de liste.',
            'valeurDeBase.min'                 => 'Le nombre de valeurs de base doit correspondre au nombre de clés de valeur.',
            'valeurDeBase.max'                 => 'Le nombre de valeurs de base ne peut pas dépasser le nombre de clés de valeur.',
            'valeurDeBase.*.value.required'    => 'Chaque valeur de base doit être renseignée.',
            'valeurDeBase.*.keyId.exists'      => 'La clé associée à la valeur de base est invalide.',

            'anneesCible.array'                => 'Les années cibles doivent être envoyées sous forme de liste.',
            'anneesCible.*.annee.required'     => 'Veuillez renseigner l’année cible.',
            'anneesCible.*.annee.distinct'     => 'Chaque année cible doit être unique.',
            'anneesCible.*.annee.date_format'  => 'L’année cible doit être au format YYYY.',
            'anneesCible.*.annee.after_or_equal' => 'L’année cible ne peut pas être antérieure à l’année de base.',
            'anneesCible.*.valeurCible.required' => 'Veuillez renseigner la valeur cible pour cette année.',
            'anneesCible.*.valeurCible.array'  => 'La valeur cible doit être fournie sous forme de liste si l’indicateur est agrégé.',
            'anneesCible.*.valeurCible.*.value.required' => 'Chaque valeur cible doit être renseignée.',
            'anneesCible.*.valeurCible.*.keyId.exists'   => 'La clé associée à la valeur cible est invalide.',

            'sites.array'                       => 'Les sites doivent être fournis sous forme de liste.',
            'sites.min'                         => 'Veuillez sélectionner au moins un site.',
            'sites.*.distinct'                  => 'Chaque site doit être unique.',
            'sites.*.exists'                    => 'Le site sélectionné est invalide.',
        ];
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
