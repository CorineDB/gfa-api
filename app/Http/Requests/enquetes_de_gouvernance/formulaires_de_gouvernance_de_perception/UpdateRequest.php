<?php

namespace App\Http\Requests\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception;

use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
use App\Rules\DistinctAttributeRule;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-un-formulaire-de-gouvernance") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->formulaire_de_perception))
        {
            $this->formulaire_de_perception = FormulaireDePerceptionDeGouvernance::findByKey($this->formulaire_de_perception);
        }

        return [
            'libelle'  => ['sometimes','max:255', Rule::unique('formulaires_de_perception_de_gouvernance', 'libelle')->where("programmeId", auth()->user()->programmeId)->ignore($this->formulaire_de_perception)->whereNull('deleted_at')],

            'description' => 'nullable|max:255',

            'perception' => ["sometimes","array", "min:1"],
            'perception.options_de_reponse' => ['sometimes', "array", "min:2"],
            'perception.options_de_reponse.*.id' => ["required", "distinct", new HashValidatorRule(new OptionDeReponseGouvernance())],
            'perception.options_de_reponse.*.point' => ["required", "numeric", "min:0", "max:1"],

            'perception.principes_de_gouvernance' => ["sometimes", "array", "min:1"],
            'perception.principes_de_gouvernance.*.id' => ["required", "distinct", new HashValidatorRule(new PrincipeDeGouvernancePerception())],
            'perception.principes_de_gouvernance.*.position' => ["sometimes", new DistinctAttributeRule(), "min:1"],
            'perception.principes_de_gouvernance.*.questions_operationnelle' => ["required", "array", "min:1"],
            //'perception.principes_de_gouvernance.*.questions_operationnelle.*' => ["sometimes", new DistinctAttributeRule(), new HashValidatorRule(new QuestionOperationnelle())],
            'perception.principes_de_gouvernance.*.questions_operationnelle.*.id' => ["sometimes", new DistinctAttributeRule(), new HashValidatorRule(new QuestionOperationnelle())],
            'perception.principes_de_gouvernance.*.questions_operationnelle.*.position' => ["sometimes", new DistinctAttributeRule(), "min:1"],
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
            // General
            'nom.sometimes' => 'Le champ "Nom" est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.unique' => 'Ce nom est déjà utilisé pour ce programme.',

            // Perception global
            'perception.sometimes' => 'La section perception est obligatoire.',
            'perception.array' => 'La section perception doit être un tableau.',
            'perception.min' => 'La section perception doit contenir au moins deux éléments.',

            // Options de réponse
            'perception.options_de_reponse.sometimes' => 'Les options de réponse sont requises.',
            'perception.options_de_reponse.array' => 'Les options de réponse doivent être un tableau.',
            'perception.options_de_reponse.min' => 'Au moins deux options de réponse sont requises.',
            'perception.options_de_reponse.*.id.required' => 'Chaque option de réponse doit avoir un identifiant.',
            'perception.options_de_reponse.*.id.distinct' => 'Les identifiants des options de réponse doivent être uniques.',
            'perception.options_de_reponse.*.point.required' => 'Chaque option doit avoir un score.',
            'perception.options_de_reponse.*.point.numeric' => 'Le score doit être un nombre.',
            'perception.options_de_reponse.*.point.min' => 'Le score minimum autorisé est 0.',
            'perception.options_de_reponse.*.point.max' => 'Le score maximum autorisé est 1.',

            // Principes de gouvernance
            'perception.principes_de_gouvernance.sometimes' => 'Au moins un principe de gouvernance est requis.',
            'perception.principes_de_gouvernance.array' => 'Les principes doivent être une liste.',
            'perception.principes_de_gouvernance.*.id.required' => 'Veuillez precisez le principe.',
            'perception.principes_de_gouvernance.*.id.distinct' => 'Veuillez precisez different principe.',

            // Questions opérationnelles
            'perception.principes_de_gouvernance.*.questions_operationnelle.required' => 'Chaque principe doit contenir au moins une question opérationnelle.',
            'perception.principes_de_gouvernance.*.questions_operationnelle.array' => 'Les questions doivent être une liste.',
            'perception.principes_de_gouvernance.*.questions_operationnelle.*.required' => 'Chaque question opérationnelle est obligatoire.',


        ];
    }
}
