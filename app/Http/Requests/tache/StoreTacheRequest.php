<?php

namespace App\Http\Requests\tache;

use App\Models\Activite;
use App\Models\Tache;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreTacheRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("creer-une-tache") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $activite = Activite::findByKey(request()->input('activiteId'));
        return [
            'nom' => 'required',
            //'statut' => 'required|integer|min:-2|max:2',
            'poids' => ['nullable', 'numeric', 'min:0'],
            'activiteId' => ['required', new HashValidatorRule(new Activite())],
            'debut' => ["required", "date", "date_format:Y-m-d", function($attribute, $value, $fail) use ($activite) {

                if($activite->dureeActivite->debut > $value){
                    $fail("La date de debut de la tache est anterieur à celui de l'activite");
                }
                
            }],
            'fin' => ["required", "date", "date_format:Y-m-d", "after_or_equal:debut", function($attribute, $value, $fail) use ($activite) {

                if($activite->dureeActivite->fin < $value){
                    $fail("La date de fin de la tache est superieur à celui de l'activite");
                }
                
            }],
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
            'nom.required' => 'Le nom de la tache est obligatoire.',
            //'statut.required' => 'Le statut de la tache est obligatoire.',
            'poids.required' => 'Le poids de la tache est obligatoire.',
            'debut.required' => 'La date de debut de la tache est obligatoire.',
            'fin.required' => 'La date de fin de la tache est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de la tache est obligatoire.',
            'activiteId.required' => 'L\'activité est obligatoire'
        ];
    }
}
