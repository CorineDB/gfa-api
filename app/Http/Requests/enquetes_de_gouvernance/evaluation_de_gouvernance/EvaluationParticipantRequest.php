<?php

namespace App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use Illuminate\Foundation\Http\FormRequest;

class EvaluationParticipantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (request()->input('nbreParticipants') !== null) {
            return (request()->user()->hasPermissionTo("ajouter-nombre-de-participant") || request()->user()->hasRole("unitee-de-gestion") || request()->user()->hasRole("organisation")) && $this->evaluation_de_gouvernance->statut == 0;
        } else if (request()->input('participants') !== null) {

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
        if (is_string($this->evaluation_de_gouvernance)) {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }

        return [
            //'organisationId'   => ['sometimes', Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],
            'nbreParticipants'                  => ['sometimes', 'numeric', 'min:0'],
            'participants'                      => ['required', 'array', 'min:1'],
            'participants.*.type_de_contact'    => ['required', 'string', 'in:email,contact'],
            'participants.*.email'              => ['nullable', 'distinct', 'email', 'max:255', function ($attribute, $value, $fail) {

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

            // --- nbreParticipants ---
            'nbreParticipants.numeric' => 'Le nombre de participants doit être un nombre.',
            'nbreParticipants.min'     => 'Le nombre de participants ne peut pas être négatif.',

            // --- participants ---
            'participants.required' => 'Vous devez ajouter au moins un participant.',
            'participants.array'    => 'La liste des participants est invalide.',
            'participants.min'      => 'Vous devez ajouter au moins un participant.',

            // --- type_de_contact ---
            'participants.*.type_de_contact.required' => 'Le type de contact doit être renseigné pour chaque participant.',
            'participants.*.type_de_contact.in'       => 'Le type de contact doit être soit "email", soit "contact".',

            // --- email ---
            'participants.*.email.email'     => 'Veuillez saisir une adresse email valide.',
            'participants.*.email.max'       => 'L’adresse email ne doit pas dépasser 255 caractères.',
            'participants.*.email.distinct'  => 'Chaque adresse email doit être unique.',

            // Message personnalisé du callback dans rules()
            'participants.*.email.required_if' => 'Veuillez renseigner l’adresse email du participant lorsque le type de contact est "email".',

            // --- phone ---
            'participants.*.phone.numeric'        => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
            'participants.*.phone.digits_between' => 'Le numéro de téléphone doit contenir entre 8 et 24 chiffres.',
            'participants.*.phone.distinct'       => 'Chaque numéro de téléphone doit être unique.',

            // Message personnalisé du callback dans rules()
            'participants.*.phone.required_if' => 'Veuillez renseigner le numéro de contact du participant lorsque le type de contact est "contact".',
        ];
    }
}
