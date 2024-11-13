<?php

namespace App\Http\Requests\organisation;

use App\Models\Organisation;
use App\Models\Programme;
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
            'nom'           => ['sometimes','max:255', Rule::unique('users', 'nom')->ignore($this->organisation->user)->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'contact'       => ['sometimes','max:8', Rule::unique('users', 'contact')->ignore($this->organisation->user)->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'email'         => ['sometimes','email','max:255', Rule::unique('users', 'email')->ignore($this->organisation->user)->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],

            'nom_point_focal'       => ['sometimes','max:50', Rule::unique('organisations', 'nom_point_focal')->ignore($this->organisation)->whereNull('deleted_at')],
            'prenom_point_focal'    => ['sometimes','max:50', Rule::unique('organisations', 'prenom_point_focal')->ignore($this->organisation)->whereNull('deleted_at')],
            'contact_point_focal'   => ['sometimes', 'numeric','digits_between:8,24', Rule::unique('organisations', 'contact_point_focal')->ignore($this->organisation)->whereNull('deleted_at')],

            'sigle'         => ['nullable','string','max:255', Rule::unique('organisations', 'sigle')->ignore($this->organisation)->whereNull('deleted_at')],
            'code'          => ['numeric', "min:2", Rule::unique('organisations', 'code')->ignore($this->organisation)->whereNull('deleted_at')],

            'longitude'         => 'sometimes|max:255',
            'latitude'          => 'sometimes|max:255',
            'addresse'          => 'sometimes|max:255',
            'quartier'          => 'sometimes|max:255',
            'arrondissement'    => 'sometimes|max:255',
            'commune'           => 'sometimes|max:255',
            'departement'       => 'sometimes|max:255',
            'pays'              => 'sometimes|max:255',
            'secteurActivite'      => 'sometimes|max:255',
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
