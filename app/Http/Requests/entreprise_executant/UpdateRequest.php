<?php

namespace App\Http\Requests\entreprise_executant;

use App\Models\EntrepriseExecutant;
use App\Models\MOD;
use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
    private $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->user = request()->user();
        return $this->user->hasRole("mod", "unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->entreprise_executant))
        {
            $this->entreprise_executant = EntrepriseExecutant::findByKey($this->entreprise_executant);
        }
        
        $rules = [
            'nom'           => ['required','max:255', Rule::unique('users')->ignore($this->entreprise_executant->user)->whereNull('deleted_at')],
            'contact'       => ['required','max:8', Rule::unique('users')->ignore($this->entreprise_executant->user)->whereNull('deleted_at')],
            'email'         => ['required','email','max:255', Rule::unique('users')->ignore($this->entreprise_executant->user)->whereNull('deleted_at')],
            'modId'         => [Rule::requiredIf($this->user->type != 'mod'), new HashValidatorRule(new MOD())],
            'programmeId'   => ['required', new HashValidatorRule(new Programme())]
        ];

        if($this->user()->hasRole("mod"))
        { 
            unset($rules['modId']);
        }

        return $rules;
    }
   

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nom.required'                      => 'Veuillez préciser votre nom.',
            'contact.required'                  => 'Veuillez préciser votre numéro de téléphone.',
            'email.email'                       => 'Veuillez préciser une adresse email valide.',
            'email.unique'                      => 'Une adresse email avait déjà été enrégistré.',
            'modId.required'                    => 'Veuillez préciser le mod qui à recruter l\'entreprise executant pour ce programme.',
            'modId.exists'                      => 'MOD inconnue. Veuillez préciser un mod existant.',
            'programmeId.required'              => 'Veuillez préciser le programme auquelle sera associé l\'entreprise executant .',
            'programmeId.exists'                => 'Programme inexistant. Veuillez préciser un programme existant.'
        ];
    }
}
