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
            'responsables.ug'               => [Rule::requiredIf(!request()->input('responsables.organisations')), !empty(request()->input('responsables.organisations')) ? 'nullable' : '', 'string', new HashValidatorRule(new UniteeDeGestion())],
            'responsables.organisations'    => [Rule::requiredIf(empty(request()->input('responsables.ug')) === true), 'array', 'min:0'],
            'responsables.organisations.*'  => ['distinct', 'string', new HashValidatorRule(new Organisation())],

            'anneeDeBase'                   => ['nullable', 'date_format:Y', 'after_or_equal:' . Carbon::parse($programme->debut)->year, 'before_or_equal:' . Carbon::parse($programme->fin)->year, 'before_or_equal:' . now()->format("Y")],

            "type_de_variable"              => ["sometimes", "in:quantitatif,qualitatif,dichotomique"],

            "agreger"                       => ["required", "boolean:false"],

            'uniteeMesureId'                => ['nullable', Rule::requiredIf(!empty(request()->input('valeurDeBase')) || count(request()->input('anneesCible')) > 0), new HashValidatorRule(new Unitee())],

            "indice"                        => ["required", "integer", "min:0"],
            'categorieId'                   => ['required', new HashValidatorRule(new Categorie())],

            'value_keys'                    => [Rule::requiredIf(request()->input('agreger')), request()->input('agreger') ? "array" : "", function ($attribute, $value, $fail) {
                if (!request()->input('agreger') && (is_array(request()->input('value_keys')))) {
                    $fail("Champ non requis.");
                }
            }, request()->input('agreger') ? "min:1" : ""],
            'value_keys.*.id'               => [Rule::requiredIf(request()->input('agreger')), "string", 'distinct', new HashValidatorRule(new IndicateurValueKey())],
            'value_keys.*.uniteeMesureId'   => ["nullable", "string", new HashValidatorRule(new Unitee())],


            'valeurDeBase'                  => ['nullable', request()->input('agreger') ? "array" : "", function ($attribute, $value, $fail) {
                if (!request()->input('agreger') && (is_array(request()->input('valeurDeBase')))) {
                    $fail("La valeur de base pour cet indicateur ne peut pas etre un array.");
                }
            }, request()->input('agreger') ? "min:" . count(request()->input('value_keys')) : "", request()->input('agreger') ? "max:" . count(request()->input('value_keys')) : ""],
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
            // --- NOM ---
            'nom.required' => 'Veuillez saisir le nom de l’indicateur.',
            'nom.string'   => 'Le nom de l’indicateur doit contenir des lettres et des chiffres uniquement.',
            'nom.unique'   => 'Un indicateur portant ce nom existe déjà. Veuillez en choisir un autre.',

            // --- RESPONSABLES ---
            'responsables.ug.required_if'        => 'Veuillez sélectionner l’unité de gestion responsable.',
            'responsables.organisations.required_if' => 'Veuillez sélectionner au moins une organisation responsable.',
            'responsables.organisations.*.distinct' => 'Chaque organisation doit être unique.',

            'responsables.ug.required_if'        => 'Veuillez sélectionner une unité de gestion responsable.',
            'responsables.organisations.required_if' => 'Veuillez sélectionner au moins une organisation responsable.',
            'responsables.organisations.*.distinct' => 'Chaque organisation responsable doit être unique.',
            'responsables.organisations.*.exists' => 'L’organisation sélectionnée est invalide.',



            // --- ANNEE DE BASE ---
            'anneeDeBase.date_format'     => 'Veuillez saisir l’année de base au format AAAA.',
            'anneeDeBase.after_or_equal'  => 'L’année de base doit être après le début du programme.',
            'anneeDeBase.before_or_equal' => 'L’année de base ne peut pas dépasser l’année en cours ni la fin du programme.',

            // --- TYPE DE VARIABLE ---
            'type_de_variable.in' => 'Le type de variable doit être : quantitatif, qualitatif ou dichotomique.',

            // --- AGREGER ---
            'agreger.required' => 'Veuillez préciser si l’indicateur doit être agrégé.',
            'agreger.boolean'  => 'La valeur doit être "Oui" ou "Non".',

            // --- UNITE DE MESURE ---
            'uniteeMesureId.required' => 'Veuillez sélectionner l’unité de mesure.',
            'uniteeMesureId.exists'   => 'Unité de mesure invalide.',

            // --- INDICE ---
            'indice.required' => 'Veuillez saisir l’indice de l’indicateur.',
            'indice.integer'  => 'L’indice doit être un nombre entier.',
            'indice.min'      => 'L’indice ne peut pas être négatif.',

            // --- CATEGORIE ---
            'categorieId.required' => 'Veuillez sélectionner une catégorie pour cet indicateur.',
            'categorieId.exists'   => 'La catégorie sélectionnée est invalide.',

            // --- VALUE_KEYS ---
            'value_keys.required_if'        => 'Veuillez sélectionner au moins une clé de mesure.',
            'value_keys.array'              => 'Les clés doivent être envoyées sous forme de liste.',
            'value_keys.min'                => 'Vous devez sélectionner au moins une clé de mesure.',
            'value_keys.*.id.required_if'   => 'Chaque clé de mesure doit avoir un identifiant.',
            'value_keys.*.id.distinct'      => 'Chaque clé de mesure doit être unique.',
            'value_keys.*.uniteeMesureId.string' => 'L’unité de mesure pour chaque clé doit être valide.',

            // --- VALEUR DE BASE ---
            'valeurDeBase.array'        => 'Veuillez saisir les valeurs de base sous forme de liste.',
            'valeurDeBase.min'          => 'Chaque valeur de base doit correspondre à une clé de mesure.',
            'valeurDeBase.max'          => 'Vous ne pouvez pas avoir plus de valeurs de base que de clés de mesure.',
            'valeurDeBase.*.value.required' => 'Chaque valeur de base doit être renseignée.',
            'valeurDeBase.*.keyId.invalid'  => 'La clé sélectionnée n’existe pas ou n’est pas valide.',

            // --- ANNEES CIBLE ---
            'anneesCible.array'             => 'Veuillez saisir les années cibles sous forme de liste.',
            'anneesCible.min'               => 'Vous devez saisir au moins une année cible.',
            'anneesCible.*.annee.required'  => 'Veuillez saisir l’année cible.',
            'anneesCible.*.annee.distinct'  => 'Chaque année cible doit être unique.',
            'anneesCible.*.annee.date_format'    => 'L’année cible doit être au format AAAA.',
            'anneesCible.*.annee.after_or_equal' => 'L’année cible ne peut pas être avant l’année de base.',
            'anneesCible.*.valeurCible.*.value.required' => 'Chaque valeur cible doit être renseignée.',
            'anneesCible.*.valeurCible.*.keyId.invalid' => 'La clé sélectionnée pour la valeur cible n’est pas valide.',

            // --- SITES ---
            'sites.array'      => 'Veuillez fournir les sites sous forme de liste.',
            'sites.min'        => 'Veuillez sélectionner au moins un site.',
            'sites.*.distinct' => 'Chaque site doit être unique.',

            // --- BAILLEUR ---
            'bailleurId.required' => 'Veuillez sélectionner le bailleur.',
            'bailleurId.exists'   => 'Le bailleur sélectionné est invalide.',
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
            'uniteeMesureId.exists'     => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système'
        ];
    }
}
