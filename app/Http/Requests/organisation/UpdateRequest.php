<?php

namespace App\Http\Requests\organisation;

use App\Models\Fond;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    private $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-une-organisation") || request()->user()->hasRole("unitee-de-gestion");

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

        if(is_string($this->organisation))
        {
            $this->organisation = Organisation::findByKey($this->organisation);
        }

        $rules = [
            'nom'           => ['sometimes','max:255', Rule::unique('users', 'nom')->ignore($this->organisation->user)->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'contact'       => ['sometimes', 'numeric','digits_between:8,24', Rule::unique('users', 'contact')->ignore($this->organisation->user)->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'email'         => ['sometimes','email','max:50', Rule::unique('users', 'email')->ignore($this->organisation->user)->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],

            'nom_point_focal'       => ['sometimes','max:50'/* , Rule::unique('organisations', 'nom_point_focal')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at') */],
            'prenom_point_focal'    => ['sometimes','max:50'/* , Rule::unique('organisations', 'prenom_point_focal')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at') */],
            'contact_point_focal'   => ['sometimes', 'numeric','digits_between:8,24'/* , Rule::unique('organisations', 'contact_point_focal')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at') */],

            'sigle'                 => ['nullable','string','max:255', Rule::unique('organisations', 'sigle')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at')],
            'code'                  => [Rule::requiredIf((request()->user()->type === 'unitee-de-gestion' || get_class(request()->user()->profilable) == UniteeDeGestion::class)), 'sometimes','numeric', "min:2", Rule::unique('organisations', 'code')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at') ],

            'type'                  => 'sometimes|string|in:osc_partenaire,osc_fosir,autre_osc,acteurs,structure_etatique',  // Ensures the value is either 'osc' or 'osc_fosir'

            'fondId'                => [Rule::requiredIf((request()->input('type') === 'osc_fosir')), (request()->input('type') != 'osc_fosir') ? 'nullable' : '', new HashValidatorRule(new Fond())],

            'latitude'              => ['nullable', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'longitude'             => ['nullable', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],

            'addresse'              => 'nullable|max:255',
            'quartier'              => 'nullable|max:255',
            'arrondissement'        => 'nullable|max:255',
            'commune'               => 'nullable|max:255',
            'departement'           => 'nullable|max:255',
            'pays'                  => 'nullable|max:255',
            'secteurActivite'       => 'nullable|max:255',
        ];

        return $rules;
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nom.sometimes' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom est déjà utilisé pour ce programme.',

            'contact.sometimes' => 'Le contact est obligatoire.',
            'contact.max' => 'Le contact ne doit pas dépasser 8 caractères.',
            'contact.unique' => 'Ce contact est déjà enregistré.',

            'email.sometimes' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.max' => 'L\'email ne doit pas dépasser 50 caractères.',
            'email.unique' => 'Cet email est déjà enregistré.',

            'nom_point_focal.sometimes' => 'Le nom du point focal est obligatoire.',
            'nom_point_focal.max' => 'Le nom du point focal ne doit pas dépasser 50 caractères.',
            'nom_point_focal.unique' => 'Ce nom de point focal est déjà utilisé.',

            'prenom_point_focal.sometimes' => 'Le prénom du point focal est obligatoire.',
            'prenom_point_focal.max' => 'Le prénom du point focal ne doit pas dépasser 50 caractères.',
            'prenom_point_focal.unique' => 'Ce prénom de point focal est déjà utilisé.',

            'contact_point_focal.sometimes' => 'Le contact du point focal est obligatoire.',
            'contact_point_focal.numeric' => 'Le contact doit être un nombre.',
            'contact_point_focal.digits_between' => 'Le contact doit avoir entre 8 et 24 chiffres.',
            'contact_point_focal.unique' => 'Ce contact est déjà utilisé.',

            'sigle.max' => 'Le sigle ne doit pas dépasser 255 caractères.',
            'sigle.unique' => 'Ce sigle est déjà utilisé.',

            'code.required_if' => 'Le code est obligatoire pour une unité de gestion.',
            'code.numeric' => 'Le code doit être un nombre.',
            'code.min' => 'Le code doit contenir au moins 2 chiffres.',
            'code.unique' => 'Ce code est déjà enregistré.',

            'type.required' => 'Le type est obligatoire.',
            'type.in' => "Le type doit être l'un de c'est element osc_partenaire, osc_fosir, autre_osc, acteurs, structure_etatique.",


            'fondId.required_if' => 'Le fond est obligatoire pour les organisations de type "osc_fosir".',

            'latitude.required' => 'La latitude est obligatoire.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.regex' => 'La latitude n\'est pas valide.',

            'longitude.required' => 'La longitude est obligatoire.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.regex' => 'La longitude n\'est pas valide.',

            'addresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'quartier.max' => 'Le quartier ne doit pas dépasser 255 caractères.',
            'arrondissement.max' => 'L\'arrondissement ne doit pas dépasser 255 caractères.',
            'commune.max' => 'La commune ne doit pas dépasser 255 caractères.',
            'departement.max' => 'Le département ne doit pas dépasser 255 caractères.',
            'pays.max' => 'Le pays ne doit pas dépasser 255 caractères.',
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
