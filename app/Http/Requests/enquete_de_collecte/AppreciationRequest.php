<?php

namespace App\Http\Requests\enquete_de_collecte;

use App\Models\Enquete;
use App\Models\EntrepriseExecutant;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppreciationRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasRole("administrateur", "super-admin", "unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->enquete_de_collecte))
        {
            $this->enquete_de_collecte = Enquete::findByKey($this->enquete_de_collecte);
        }

        return [
            'contenu'          => ['required', 'string', 'max:255'],
            'type'             => 'required|string|in:faiblesse,recommendation',  // Ensures the value is either 'faiblesse' or 'recommendation'
            'organisationId'   => ['required', new HashValidatorRule(new EntrepriseExecutant())],
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
            "type.required"                     => "Le type d'indicateur est obligatoire.",
            "type.in"                           => "Le type doit être soit faiblesse, soit recommandation.",
            "principeable_id.required"          => "Le champ principeable ID est obligatoire.",
            "principeable_id.exists"            => "L'ID fourni n'existe pas dans la table liée.",
            

            // Custom messages for the 'organisationId' field
            'organisationId.required' => 'Le champ organisation est obligatoire.',        
        ];
    }
}
