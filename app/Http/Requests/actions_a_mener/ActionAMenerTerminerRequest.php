<?php

namespace App\Http\Requests\actions_a_mener;

use App\Models\ActionAMener;
use App\Models\EvaluationDeGouvernance;
use App\Models\UniteeDeGestion;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class ActionAMenerTerminerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if(request()->user()->hasPermissionTo("signaler-une-action-a-mener-est-realise")){

            if(is_string($this->action_a_mener))
            {
                $this->action_a_mener = ActionAMener::findByKey($this->action_a_mener);
            }
                
            return request()->user()->hasPermissionTo("signaler-une-action-a-mener-est-realise") && $this->action_a_mener->statut > -1 && $this->action_a_mener->statut < 2 && $this->action_a_mener->est_valider == false;
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
            'preuves'                       => ['required', "array", "min:1"],
            'preuves.*'                     => ['required', "file", 'mimes:txt,doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv', /* 'mimetypes:text/plain,text/csv,application/pdf,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,image/gif,audio/mpeg,audio/wav,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska', */ "max:20480"],
            'commentaire'                   => ['required', "string"]
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
