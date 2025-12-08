<?php

namespace App\Http\Requests\bailleur;

use App\Models\Programme;
use App\Models\UniteeDeGestion;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
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
        return [
            'nom'           => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'code'          => [Rule::requiredIf((request()->user()->type === 'unitee-de-gestion' || get_class(request()->user()->profilable) == UniteeDeGestion::class)), 'numeric'],
            'contact'       => ['required', 'numeric','digits_between:8,24', Rule::unique('users')->whereNull('deleted_at')],
            'email'         => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'sigle'         => ['required','string','max:255', Rule::unique('bailleurs')->whereNull('deleted_at')],
            'pays'          => 'required|string|max:255',
            'logo'          => 'nullable|mimes:jpg,png,jpeg,webp,svg,ico|max:2048',
            'programmeId'   => ['required', new HashValidatorRule(new Programme()), function(){

                $programme = Programme::findByKey($this->programmeId);

                if( $programme->codeBailleur($this->programmeId) === $this->code){
                   throw ValidationException::withMessages(['code' => "Ce code est déjà attribué à un autre bailleur."]);
                }
            }]
        ];
    }
}
