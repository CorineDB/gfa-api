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
            'contact'       => ['sometimes','max:8', Rule::unique('users', 'contact')->ignore($this->organisation->user)->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'email'         => ['sometimes','email','max:255', Rule::unique('users', 'email')->ignore($this->organisation->user)->where("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],

            'nom_point_focal'       => ['sometimes','max:50', Rule::unique('organisations', 'nom_point_focal')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at')],
            'prenom_point_focal'    => ['sometimes','max:50', Rule::unique('organisations', 'prenom_point_focal')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at')],
            'contact_point_focal'   => ['sometimes', 'numeric','digits_between:8,24', Rule::unique('organisations', 'contact_point_focal')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at')],

            'sigle'                 => ['nullable','string','max:255', Rule::unique('organisations', 'sigle')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at')],
            'code'                  => [Rule::requiredIf((request()->user()->type === 'unitee-de-gestion' || get_class(request()->user()->profilable) == UniteeDeGestion::class)), 'numeric', "min:2", Rule::unique('organisations', 'code')->where("programmeId", request()->user()->programmeId)->ignore($this->organisation)->whereNull('deleted_at') ],

            'type'                  => 'required|string|in:osc,osc_fosir',  // Ensures the value is either 'osc' or 'osc_fosir'

            'fondId'                => [Rule::requiredIf((request()->input('type') === 'osc_fosir')), new HashValidatorRule(new Fond())],
            'latitude'              => ['required', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'longitude'             => ['required', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],

            'addresse'              => 'sometimes|max:255',
            'quartier'              => 'sometimes|max:255',
            'arrondissement'        => 'sometimes|max:255',
            'commune'               => 'sometimes|max:255',
            'departement'           => 'sometimes|max:255',
            'pays'                  => 'sometimes|max:255',
            'secteurActivite'       => 'sometimes|max:255',
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
            'nom.required'                      => 'Veuillez préciser votre nom.',
            'contact.required'                  => 'Veuillez préciser votre numéro de téléphone.',
            'email.email'                       => 'Veuillez préciser une adresse email valide.',
            'email.unique'                      => 'Une adresse email avait déjà été enrégistré.',
            'programmeId.required'              => 'Veuillez préciser le programme auquelle sera associé l\'entreprise executant .',
            'programmeId.exists'                => 'Programme inexistant. Veuillez préciser un programme existant.'
        ];
    }
}
