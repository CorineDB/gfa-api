<?php

namespace App\Http\Requests\indicateur;

use App\Models\Indicateur;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\ValidationException;

class AddStrutureResponsableRequest extends FormRequest
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

        if(is_string($this->indicateur))
        {
            $this->indicateur = Indicateur::findByKey($this->indicateur);
        }

        return [
            
           'responsables'                  => ['required', 'array'],
           'responsables.ug'               => [Rule::requiredIf(!request()->input('responsables.organisations')), !empty(request()->input('responsables.organisations')) ? 'nullable' :'', 'string', new HashValidatorRule(new UniteeDeGestion())],
           'responsables.organisations'    => [Rule::requiredIf(empty(request()->input('responsables.ug')) === true), 'array', 'min:0'],

           'responsables.organisations.*'  => ['distinct', 'string', new HashValidatorRule(new Organisation())],

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
            'value_keys.required' => 'Le champ des clés de valeur est requis.',
            'value_keys.array'    => 'Le champ des clés de valeur doit être un tableau.',
            'value_keys.min'      => 'Au moins une clé de valeur est requise.',
            'value_keys.*.id.required' => 'Chaque clé de valeur doit avoir un identifiant.',
            'value_keys.*.id.string'   => 'Chaque identifiant de clé de valeur doit être une chaîne de caractères.',
            'value_keys.*.id.distinct' => 'Chaque identifiant de clé de valeur doit être unique.',
            'value_keys.*.uniteeMesureId.string' => 'L’identifiant de l’unité de mesure doit être une chaîne de caractères.',
            'value_keys.*.id.custom'   => 'Cette clé est déjà rattachée à l\'indicateur.', // Message personnalisé pour la règle de validation custom
        ];
    }
}
