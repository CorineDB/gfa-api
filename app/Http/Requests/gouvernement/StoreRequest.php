<?php

namespace App\Http\Requests\gouvernement;

use App\Models\Programme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        return true;
    }

    protected function prepareForValidation(): void
    {

        $programme = Programme::findByKey($this->programmeId);

        if(!isset($programme->id))
            throw ValidationException::withMessages(['programmeId' => "Programme inconnu"]);

        if( isset($programme->programme) ) throw ValidationException::withMessages(['programmeId' => "Un compte gouvernement avait déjà été crée pour ce programme."]);

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
            'nom'                       => ['required','max:255', Rule::unique('users','nom')->whereNull('deleted_at')],
            'contact'                   => ['required','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'email'                     => ['required','email','max:255', Rule::unique('users')->whereNull('deleted_at')],
            'programmeId'               => ['required', 'int', Rule::exists('programmes','id')->whereNull('deleted_at')]
        ];
    }
}
