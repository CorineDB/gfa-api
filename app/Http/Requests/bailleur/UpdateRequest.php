<?php

namespace App\Http\Requests\bailleur;

use App\Models\Programme;
use App\Rules\HashValidatorRule;
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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom'           => ['nullable','max:255'/*, Rule::unique('users', 'nom')->ignore($this->bailleur->user)*/],
            'code'          => 'nullable|numeric',
            'contact'       => ['nullable','numeric','digits_between:8,24'/*, Rule::unique('users', 'contact')->ignore($this->bailleur->user)*/],
            //'email'         => ['nullable','email','max:255', Rule::unique('users')->ignore($this->bailleur->user)->whereNull('deleted_at')],
            'sigle'         => ['nullable','string','max:255', /*Rule::unique('bailleurs', 'sigle')->ignore($this->bailleur)*/],
            'pays'          => 'nullable|string|max:255',
            'logo'          => 'nullable|mimes:jpg,png,jpeg,webp,svg,ico|max:2048',
            /*'programmeId'   => ['sometimes','required', new HashValidatorRule(new Programme()), function(){

                $programme = Programme::findByKey($this->programmeId);

                if(isset($this->code))
                {
                    if( $programme->codeBailleur($this->programmeId) === $this->code){
                        throw ValidationException::withMessages(['code' => "Ce code est déjà attribué à un autre bailleur."]);
                     }
                }

            }]*/
        ];
    }
}
