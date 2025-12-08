<?php

namespace App\Http\Requests\surveys\forms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("creer-un-formulaire-individuel") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            // ---------- LIBELLE ----------
            'libelle.required' => 'Le libellé du formulaire d’enquête personnalisée est obligatoire.',
            'libelle.string'   => 'Le libellé du formulaire doit être une chaîne de caractères valide.',
            'libelle.unique'   => 'Un formulaire d’enquête personnalisée avec ce libellé existe déjà.',

            // ---------- DESCRIPTION ----------
            'description.max'  => 'La description du formulaire ne peut pas dépasser 255 caractères.',

            // ---------- FORM DATA ----------
            'form_data.required' => 'Le contenu du formulaire d’enquête personnalisée est obligatoire.',
            'form_data.array'    => 'Le contenu du formulaire doit être une structure de données valide.',
            'form_data.min'      => 'Le formulaire doit contenir au moins un élément.',
        ];

        return [
            'libelle'               => ['required', 'string', Rule::unique('survey_forms', 'libelle')->where("created_by_type", auth()->user()->profilable_type)->where("created_by_id", auth()->user()->profilable_id)->where("programmeId", auth()->user()->programmeId)->whereNull('deleted_at')],

            'description' => 'nullable|max:255',
            'form_data' => 'required|array|min:1'
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
            // Custom messages for the 'libelle' field
            'libelle.required'      => 'Le champ libelle est obligatoire.',
            'libelle.max'           => 'Le libelle ne doit pas dépasser 255 caractères.',
            'libelle.unique'        => 'Ce libelle est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'programmeId' field
            'programmeId.required' => 'Le champ programme est obligatoire.',
        ];
    }
}
