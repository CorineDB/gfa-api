<?php

namespace App\Http\Requests\tache;

use App\Models\Tache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeplacerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("modifier-une-tache") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tacheId' => ['required', Rule::exists('taches', 'id')->whereNull('deleted_at')],
            'topermute' => 'required|integer|min:0|max:1',
        ];
    }

    protected function prepareForValidation(): void
    {

        $tache = Tache::decodeKey($this->tacheId);

        if(!$tache)
            throw ValidationException::withMessages(['tacheId' => "Tache inconnue"]);


        $this->merge([
            'tacheId' => $tache
        ]);

        if($this->toPermute == 0)
        {
            if(!isset($this->position)) throw ValidationException::withMessages(['position' => "La nouvelle position est obligatoire"]);
        }

    }
}
