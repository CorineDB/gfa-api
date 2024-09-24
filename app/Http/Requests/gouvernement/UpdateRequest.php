<?php

namespace App\Http\Requests\gouvernement;

use App\Models\Gouvernement;
use App\Models\Programme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();

        return $user->hasRole("unitee-de-gestion");
    }

    protected function prepareForValidation(): void
    {

        $programme = Programme::findByKey($this->programmeId);

        if(!isset($programme->id))
            throw ValidationException::withMessages(['programmeId' => "Programme inconnu"]);

        if(is_string($this->gouvernement))
        {
            $this->gouvernement = Gouvernement::findByKey($this->gouvernement);
        }

        if( isset($programme->gouvernement) ){ 
            if( $programme->gouvernement !== $this->gouvernement ) throw ValidationException::withMessages(['programmeId' => "Un compte gouvernement avait déjà été crée pour ce programme."]);
        }

        $this->merge([
            'programmeId' => $programme->id
        ]);
    } 

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        return [
            'nom'                       => ['required','max:255', Rule::unique('users','nom')->ignore($this->unitee_de_gestion->user)->whereNull('deleted_at')],
            'contact'                   => ['required','max:255', Rule::unique('users')->ignore($this->unitee_de_gestion->user)->whereNull('deleted_at')],
            'email'                     => ['required','email','max:255', Rule::unique('users')->ignore($this->unitee_de_gestion->user)->whereNull('deleted_at')],
            'programmeId'               => ['required', 'int', Rule::exists('programmes','id')->whereNull('deleted_at')]
        ];
    }
}
