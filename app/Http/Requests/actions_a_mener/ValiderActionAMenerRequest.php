<?php

namespace App\Http\Requests\actions_a_mener;

use App\Models\ActionAMener;
use Illuminate\Foundation\Http\FormRequest;

class ValiderActionAMenerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if(request()->user()->hasRole("unitee-de-gestion")){

            if(is_string($this->action_a_mener))
            {
                $this->action_a_mener = ActionAMener::findByKey($this->action_a_mener);
                
            }
            
            return request()->user()->hasRole("unitee-de-gestion") && $this->action_a_mener->est_valider == false;
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
        return [
            'est_valider'                       => ['required', "boolean:true"],
            'commentaire'                       => ['required', "string"]
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
        ];
    }
}
