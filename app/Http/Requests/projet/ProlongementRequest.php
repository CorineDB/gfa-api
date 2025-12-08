<?php

namespace App\Http\Requests\projet;

use App\Models\Bailleur;
use App\Models\Programme;
use App\Models\Projet;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProlongementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("prolonger-un-projet") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->projet))
        {
            $this->projet = Projet::findByKey($this->projet);
        }

        return [
            'debut' => [ Rule::requiredIf(!$this->fin), 'after:'. $this->projet->debut, 'before_or_equal:'.$this->projet->fin],
            'fin' => [ Rule::requiredIf(!$this->debut), 'after:'. $this->projet->fin]
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
            'debut.required'            => 'La date de début du projet est obligatoire.',
            'debut.after'               => 'La nouvelle date de fin doit être supérieur à la date de debut actuelle du projet.',
            'debut.before_or_equal'     => 'La date de début doit être supérieur ou égale à la date de fin du projet.',
            'fin.required'              => 'La date de fin du projet est obligatoire.',
            'fin.after'                 => 'La nouvelle date de fin doit être supérieur à la date de fin actuelle du projet.',
        ];
    }
}
