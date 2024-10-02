<?php

namespace App\Http\Requests\organisation;

use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    protected $user;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->user = request()->user();
        return $this->user->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'nom'           => ['required','max:50', Rule::unique('users', 'nom')->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'contact'       => ['required', 'numeric','digits_between:8,24', Rule::unique('users', 'contact')->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],
            'email'         => ['required','email','max:50', Rule::unique('users', 'email')->whereNot("programmeId", request()->user()->programmeId)->whereNull('deleted_at')],

            'sigle'         => ['required','string','max:15', Rule::unique('organisations', 'sigle')->whereNull('deleted_at')],
            'code'          => [Rule::requiredIf(request()->user()->type === 'unitee-de-gestion'), 'numeric', Rule::unique('organisations', 'code')->whereNull('deleted_at') ],
            'programmeId'   => ['required', new HashValidatorRule(new Programme())],
        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
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
            'programmeId.required'              => 'Veuillez préciser le programme auquelle sera associé l\'entreprise executant .',
            'programmeId.exists'                => 'Programme inexistant. Veuillez préciser un programme existant.'
        ];
    }
}
