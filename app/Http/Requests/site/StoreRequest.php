<?php

namespace App\Http\Requests\site;

use App\Models\Bailleur;
use App\Models\EntrepriseExecutant;
use App\Models\Indicateur;
use App\Models\Programme;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        return request()->user()->hasPermissionTo("creer-un-site") || request()->user()->hasRole("unitee-de-gestion");

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
        return [

            'nom'               => ['required', 'string', Rule::unique('sites', 'nom')->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'quartier'          => 'nullable|string',
            'arrondissement'    => 'nullable|string',
            'commune'           => 'nullable|string',
            'departement'       => 'nullable|string',
            'pays'              => 'required|string',
            'latitude'          => ['nullable', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'longitude'         => ['nullable', 'numeric', 'regex:/^[-]?((1[0-7][0-9])|([1-9]?[0-9])|(180))(\.\d+)?$/'],
            'projetId' => ['sometimes', new HashValidatorRule(new Projet())],
            'indicateurId' => ['sometimes', new HashValidatorRule(new Indicateur())],
            /*

            'bailleurId' => ['required', new HashValidatorRule(new Bailleur())],

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
