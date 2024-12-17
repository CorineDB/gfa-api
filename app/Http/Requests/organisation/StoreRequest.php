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
            'nom'           => ['required','max:50', Rule::unique('users', 'nom')->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'contact'       => ['required', 'numeric','digits_between:8,24', Rule::unique('users', 'contact')->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'email'         => ['required','email','max:50', Rule::unique('users', 'email')->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],

            'nom_point_focal'       => ['required','max:50', Rule::unique('organisations', 'nom_point_focal')->whereNull('deleted_at')],
            'prenom_point_focal'    => ['required','max:50', Rule::unique('organisations', 'prenom_point_focal')->whereNull('deleted_at')],
            'contact_point_focal'   => ['required', 'numeric','digits_between:8,24', Rule::unique('organisations', 'contact_point_focal')->whereNull('deleted_at')],

            'sigle'             => ['required','string','max:15', Rule::unique('organisations', 'sigle')->whereNull('deleted_at')],
            'code'              => [Rule::requiredIf((request()->user()->type === 'unitee-de-gestion' || get_class(request()->user()->profilable) == UniteeDeGestion::class)), 'numeric', "min:2", Rule::unique('organisations', 'code')->whereNull('deleted_at') ],
            'type'              => 'required|string|in:osc,osc_fosir',  // Ensures the value is either 'osc' or 'osc_fosir'

            'fondId'            => ['sometimes',Rule::requiredIf((request()->input('type') === 'osc_fosir')), new HashValidatorRule(new Fond())],

            'latitude'          => ['required', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'longitude'         => ['required', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],


            'addresse'          => 'required|max:255',
            'quartier'          => 'required|max:255',
            'arrondissement'    => 'required|max:255',
            'commune'           => 'required|max:255',
            'departement'       => 'required|max:255',
            'pays'              => 'required|max:255',
            'secteurActivite'   => 'required|max:255',
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
            'nom.required'                      => 'Veuillez préciser votre nom.',
            'contact.required'                  => 'Veuillez préciser votre numéro de téléphone.',
            'email.email'                       => 'Veuillez préciser une adresse email valide.',
            'email.unique'                      => 'Une adresse email avait déjà été enrégistré.',
            'programmeId.required'              => 'Veuillez préciser le programme auquelle sera associé l\'entreprise executant .',
            'programmeId.exists'                => 'Programme inexistant. Veuillez préciser un programme existant.'
        ];
    }
}
