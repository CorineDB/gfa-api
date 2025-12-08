<?php

namespace App\Http\Requests\indicateur;

use App\Models\Indicateur;
use App\Models\IndicateurValueKey;
use App\Models\Unitee;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\ValidationException;

class AddValueKeysRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("ajouter-une-cle-de-valeur-indicateur") || request()->user()->hasRole("unitee-de-gestion");
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

        if($this->indicateur->suivis->isNotEmpty()) throw ValidationException::withMessages(["Cet indicateur a deja ete suivi et donc ne peut plus etre mis a jour."]);
        if(!$this->indicateur->agreger) throw ValidationException::withMessages(["Cet indicateur n'est pas agreger."]);

        return [
            
            'value_keys'                    => ["array", "min:1"],
            'value_keys.*.id'               => ["required", "string", 'distinct', new HashValidatorRule(new IndicateurValueKey()), function ($attribute, $value, $fail) {

                if ($this->indicateur->valueKeys()->where('indicateurValueKeyId', request()->input($attribute))->exists()) {
                    $fail("Cette cle est deja rattacher a l'indicateur.");
                }
            }],
            'value_keys.*.uniteeMesureId'   => ["nullable", "string", new HashValidatorRule(new Unitee())]
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
