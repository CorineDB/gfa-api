<?php

namespace App\Http\Requests\programme;

use App\Models\Programme;
use Illuminate\Foundation\Http\FormRequest;
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
        return request()->user()->hasPermissionTo("modifier-un-programme") || request()->user()->hasRole("administrateur", "super-admin");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => ['required', 'max:255', Rule::unique('programmes')->ignore($this->programme)->whereNull('deleted_at')],
            'code' => ['required', Rule::unique('programmes')->ignore($this->programme)->whereNull('deleted_at')],
            'budgetNational' => ['nullable', 'int', 'min:0', 'max:9999999999999', function () {
                // Vérification vers les ENFANTS (projets)
                if ($this->route('programme')) {
                    $programme = Programme::findByKey($this->route('programme'));
                    if ($programme) {
                        $totalBudgetNationalProjets = $programme->projets->sum('budgetNational');

                        if ($this->budgetNational < $totalBudgetNationalProjets) {
                            throw ValidationException::withMessages(["budgetNational" => "Le montant du fond propre alloue au programme ne peut pas etre inferieur au total des fonds propres alloues aux projets de ce programme."], 1);
                        }
                    }
                }
            }],
            'objectifGlobaux' => 'required',
            'debut' => 'required|date|date_format:Y-m',
            'fin' => 'required|date|date_format:Y-m|after_or_equal:debut'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    /* public function messages()
    {
        return [
            'nom.required' => 'Le nom du programme est obligatoire.',
            'objectifGlobaux.required' => 'Veuillez précisez l\'objectif global du programme.',
            'budgetNational.required' => 'Veuillez précisez le montant de la subvention du programme.',
            'budgetNational.integer' => 'Le montant de la subvention doit être un entier.',
            'budgetNational.min' => 'Le montant de la subvention du programme ne peut pas être inférieur à 0.',
            'budgetNational.max' => 'Le montant de la subvention ne peut pas dépasser 9 999 999 999 999 CFA.',
            'code.required' => 'Le code du programme est obligatoire.',
            'code.min' => 'Le code du programme ne peut pas être inférieur à 0.',
            'debut.required' => 'La date de début du programme est obligatoire.',
            'fin.required' => 'La date de fin du programme est obligatoire.',
        ];
    } */
    public function messages()
    {
        return [
            'nom.required' => 'Le :attribute est obligatoire.',
            'nom.max' => 'Le :attribute ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Le :attribute est déjà utilisé.',

            'code.required' => 'Le :attribute est obligatoire.',
            'code.unique' => 'Le :attribute est déjà utilisé.',

            'budgetNational.integer' => 'Le :attribute doit être un entier.',
            'budgetNational.min' => 'Le :attribute ne peut pas être inférieur à 0.',
            'budgetNational.max' => 'Le :attribute ne peut pas dépasser 9 999 999 999 999 CFA.',

            'objectifGlobaux.required' => 'Veuillez préciser :attribute.',

            'debut.required' => 'La :attribute est obligatoire.',
            'debut.date' => 'La :attribute doit être une date valide.',
            'debut.date_format' => 'La :attribute doit respecter le format YYYY-MM.',

            'fin.required' => 'La :attribute est obligatoire.',
            'fin.date' => 'La :attribute doit être une date valide.',
            'fin.date_format' => 'La :attribute doit respecter le format YYYY-MM.',
            'fin.after_or_equal' => 'La :attribute doit être postérieure ou égale à la date de début.',
        ];
    }

    public function attributes()
    {
        return [
            'nom' => 'nom du programme',
            'code' => 'code du programme',
            'budgetNational' => 'montant de subvention des projets du programme',
            'objectifGlobaux' => 'objectif global',
            'debut' => 'date de début',
            'fin' => 'date de fin',
        ];
    }
}
