<?php

namespace App\Http\Requests\site;

use App\Models\Bailleur;
use App\Models\EntrepriseExecutant;
use App\Models\Indicateur;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\Site;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
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
        $user = Auth::user();

        return $user->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->site))
        {
            $this->site = Site::findByKey($this->site);
        }

        return [

            'nom'               => ['sometimes', 'max:255', Rule::unique('sites', 'nom')->ignore($this->site->id)->whereNull('deleted_at')],
            'quartier'          => 'required|string',
            'arrondissement'    => 'required|string',
            'commune'           => 'required|string',
            'departement'       => 'required|string',
            'pays'              => 'required|string',
            'latitude'          => ['required', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'longitude'         => ['required', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'projetId' => ['sometimes', new HashValidatorRule(new Projet())],
            'indicateurId' => ['sometimes', new HashValidatorRule(new Indicateur())],
            /*'projetId' => ['sometimes', new HashValidatorRule(new Projet())],
            'indicateurId' => ['sometimes', new HashValidatorRule(new Indicateur())],

            'bailleurId' => ['sometimes|required', new HashValidatorRule(new Bailleur())],

            'entrepriseExecutanteId.*' => 'required'*/
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
            'nom.required'                      => 'Le nom de l\'indicateur est obligatoire.',
            'longitude.required'                => 'La longitude du site est obligatoire.',
            'latitude.required'                 => 'La latitude du site est obligatoire.',

            'bailleurId.required'               => 'Veuillez préciser le bailleur qui finance les travaux de ce site pour ce programme.',
            'bailleurId.exists'                 => 'Bailleur inconnu. Veuillez sélectionner un bailleur dans le système',

            'programmeId.required'               => 'Veuillez préciser le programme.',
            'programmeId.exists'                 => 'Programme inconnu. Veuillez sélectionner un programme existant'
        ];
    }
}
