<?php

namespace App\Http\Requests\organisation;

use App\Models\Fond;
use App\Models\Programme;
use App\Models\UniteeDeGestion;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    protected $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-une-organisation") || request()->user()->hasRole("unitee-de-gestion");

        $this->user = request()->user();
        return $this->user->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'nom'           => ['required','max:255', Rule::unique('users', 'nom')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'contact'       => ['required', 'numeric','digits_between:8,24', Rule::unique('users', 'contact')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'email'         => ['required','email','max:50', Rule::unique('users', 'email')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],

            'nom_point_focal'       => ['required','max:50'/* , Rule::unique('organisations', 'nom_point_focal')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at') */],
            'prenom_point_focal'    => ['required','max:50'/* , Rule::unique('organisations', 'prenom_point_focal')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at') */],
            'contact_point_focal'   => ['required', 'numeric','digits_between:8,24'/* , Rule::unique('organisations', 'contact_point_focal')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at') */],

            'sigle'             => ['required','string','max:15', Rule::unique('organisations', 'sigle')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'code'              => [Rule::requiredIf((request()->user()->type === 'unitee-de-gestion' || get_class(request()->user()->profilable) == UniteeDeGestion::class)), 'numeric', "min:2", Rule::unique('organisations', 'code')->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at') ],
            'type'              => 'required|string|in:osc_partenaire,osc_fosir,autre_osc,acteurs,structure_etatique',// Ensures the value is either 'osc' or 'osc_fosir'

            'fondId'            => [Rule::requiredIf((request()->input('type') === 'osc_fosir')), (request()->input('type') != 'osc_fosir') ? 'nullable' : '', new HashValidatorRule(new Fond())],

            'latitude'          => ['nullable', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'longitude'         => ['nullable', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],


            'addresse'          => 'nullable|max:255',
            'quartier'          => 'nullable|max:255',
            'arrondissement'    => 'nullable|max:255',
            'commune'           => 'nullable|max:255',
            'departement'       => 'nullable|max:255',
            'pays'              => 'nullable|max:255',
            'secteurActivite'   => 'nullable|max:255',
        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom est déjà utilisé dans le programme.',

            'contact.required' => 'Le contact est obligatoire.',
            'contact.numeric' => 'Le contact doit être un nombre.',
            'contact.digits_between' => 'Le contact doit contenir entre 8 et 24 chiffres.',
            'contact.unique' => 'Ce contact est déjà utilisé dans le programme.',

            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'email ne doit pas dépasser 50 caractères.',
            'email.unique' => 'Cet email est déjà utilisé dans le programme.',

            'nom_point_focal.required' => 'Le nom du point focal est obligatoire.',
            'nom_point_focal.max' => 'Le nom du point focal ne doit pas dépasser 50 caractères.',
            'nom_point_focal.unique' => 'Ce nom de point focal est déjà utilisé dans le programme.',

            'prenom_point_focal.required' => 'Le prénom du point focal est obligatoire.',
            'prenom_point_focal.max' => 'Le prénom du point focal ne doit pas dépasser 50 caractères.',
            'prenom_point_focal.unique' => 'Ce prénom de point focal est déjà utilisé dans le programme.',

            'contact_point_focal.required' => 'Le contact du point focal est obligatoire.',
            'contact_point_focal.numeric' => 'Le contact du point focal doit être un nombre.',
            'contact_point_focal.digits_between' => 'Le contact du point focal doit contenir entre 8 et 24 chiffres.',
            'contact_point_focal.unique' => 'Ce contact de point focal est déjà utilisé dans le programme.',

            'sigle.required' => 'Le sigle est obligatoire.',
            'sigle.string' => 'Le sigle doit être une chaîne de caractères.',
            'sigle.max' => 'Le sigle ne doit pas dépasser 15 caractères.',
            'sigle.unique' => 'Ce sigle est déjà utilisé dans le programme.',

            'code.required' => 'Le code est obligatoire pour une unité de gestion.',
            'code.numeric' => 'Le code doit être un nombre.',
            'code.min' => 'Le code doit avoir au moins 2 caractères.',
            'code.max' => 'Le code ne doit pas dépasser 255 caractères.',
            'code.unique' => 'Ce code est déjà utilisé dans le programme.',

            'type.required' => 'Le type est obligatoire.',
            'type.string' => 'Le type doit être une chaîne de caractères.',
            'type.in' => "Le type doit être l'un de c'est element osc_partenaire, osc_fosir, autre_osc, acteurs, structure_etatique.",

            'fondId.required_if' => 'Le fond est obligatoire pour les organisations de type "osc_fosir".',

            'latitude.required' => 'La latitude est obligatoire.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.regex' => 'La latitude n\'est pas valide.',

            'longitude.required' => 'La longitude est obligatoire.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.regex' => 'La longitude n\'est pas valide.',

            'addresse.required' => 'L\'adresse est obligatoire.',
            'addresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',

            'quartier.required' => 'Le quartier est obligatoire.',
            'quartier.max' => 'Le quartier ne doit pas dépasser 255 caractères.',

            'arrondissement.required' => 'L\'arrondissement est obligatoire.',
            'arrondissement.max' => 'L\'arrondissement ne doit pas dépasser 255 caractères.',

            'commune.required' => 'La commune est obligatoire.',
            'commune.max' => 'La commune ne doit pas dépasser 255 caractères.',

            'departement.required' => 'Le département est obligatoire.',
            'departement.max' => 'Le département ne doit pas dépasser 255 caractères.',

            'pays.required' => 'Le pays est obligatoire.',
            'pays.max' => 'Le pays ne doit pas dépasser 255 caractères.',

            'secteurActivite.required' => 'Le secteur d\'activité est obligatoire.',
            'secteurActivite.max' => 'Le secteur d\'activité ne doit pas dépasser 255 caractères.',
        ];
        return [
            'nom.required'                      => 'Veuillez préciser votre nom.',
            'contact.required'                  => 'Veuillez préciser votre numéro de téléphone.',
            'email.email'                       => 'Veuillez préciser une adresse email valide.',
            'email.unique'                      => 'Une adresse email avait déjà été enrégistré.',
            'programmeId.required'              => 'Veuillez préciser le programme auquelle sera associé l\'entreprise executant .',
            'programmeId.exists'                => 'Programme inexistant. Veuillez préciser un programme existant.'
        ];
    }
}
