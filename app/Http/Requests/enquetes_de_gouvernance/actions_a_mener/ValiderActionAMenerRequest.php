<?php

namespace App\Http\Requests\enquetes_de_gouvernance\actions_a_mener;

use App\Models\enquetes_de_gouvernance\ActionAMener;
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

        if(request()->user()->hasPermissionTo("valider-une-action-a-mener")){

            if(is_string($this->action_a_mener))
            {
                $this->action_a_mener = ActionAMener::findByKey($this->action_a_mener);

            }

            return request()->user()->hasPermissionTo("valider-une-action-a-mener") && $this->action_a_mener->statut == 2 && $this->action_a_mener->est_valider == false;
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
