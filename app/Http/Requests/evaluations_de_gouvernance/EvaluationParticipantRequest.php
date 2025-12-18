<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\EvaluationDeGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\Organisation;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class EvaluationParticipantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(request()->input('nbreParticipants') !== null){
            return (request()->user()->hasPermissionTo("ajouter-nombre-de-participant") || request()->user()->hasRole("unitee-de-gestion") || request()->user()->hasRole("organisation")) && $this->evaluation_de_gouvernance->statut == 0;
        }
        else if(request()->input('participants') !== null){

            return (request()->user()->hasPermissionTo("envoyer-une-invitation") || request()->user()->hasRole("unitee-de-gestion") || request()->user()->hasRole("organisation"))/*  && $this->evaluation_de_gouvernance->statut == 0 */;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->evaluation_de_gouvernance))
        {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }

        return [
            //'organisationId'   => ['sometimes', Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],
            'nbreParticipants'                  => ['sometimes', 'numeric', 'min:0'],
            'participants'                      => ['required', 'array', 'min:1'],
            'participants.*.type_de_contact'    => ['required', 'string', 'in:email,contact'],
            'participants.*.email'              => ['nullable', 'distinct', 'email','max:255', function ($attribute, $value, $fail) {

                // Get the index from the attribute name
                preg_match('/participants\.(\d+)\.email/', $attribute, $matches);
                $index = $matches[1] ?? null; // Get the index if it exists

                $type = request()->input('participants.*.type_de_contact')[$index];
                $email = request()->input('participants.*.email')[$index];
                
                // Ensure each keyId in valeurDeBase is one of the value_keys.id
                if ($type == 'email' && (empty($email) || is_null($email))) {
                    $fail("Veillez l'adresse email du participant.");
                }
            }],
            'participants.*.phone'            => ['nullable', 'distinct', 'numeric', 'digits_between:8,24', function ($attribute, $value, $fail) {

                // Get the index from the attribute name
                preg_match('/participants\.(\d+)\.phone/', $attribute, $matches);
                $index = $matches[1] ?? null; // Get the index if it exists

                $type = request()->input('participants.*.type_de_contact')[$index];
                $phone = request()->input('participants.*.phone')[$index];
                
                // Ensure each keyId in valeurDeBase is one of the value_keys.id
                if ($type == 'contact' && (empty($phone) || is_null($phone))) {
                    $fail("Veillez le contact du participant.");
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
            // Custom messages for the 'nom' field       
        ];
    }
}
