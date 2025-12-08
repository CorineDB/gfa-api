<?php

namespace App\Http\Requests\tache;

use App\Models\Tache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProlongementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        return request()->user()->hasPermissionTo("prolonger-une-tache") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->tache))
        {
            $this->tache = Tache::findByKey($this->tache);
        }

        return [
            'debut' => [ Rule::requiredIf(!$this->fin), 'before_or_equal:'.$this->tache->fin],
            'fin' => [ Rule::requiredIf(!$this->debut), 'after:'. $this->tache->fin]
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
            "debut.required"            => "La date de début du tache est obligatoire.",
            "debut.after"               => "La nouvelle date de fin doit être supérieur à la date de debut actuelle d'une tache.",
            "debut.before_or_equal"     => "La date de début doit être supérieur ou égale à la date de fin du tache.",
            "fin.required"              => "La date de fin du tache est obligatoire.",
            "fin.after"                 => "La nouvelle date de fin doit être supérieur à la date de fin actuelle du tache.",
        ];
    }
}
