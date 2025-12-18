<?php

namespace App\Http\Requests\duree;

use App\Models\Activite;
use App\Models\Tache;
use Illuminate\Foundation\Http\FormRequest;

class StoreDureeRequest extends FormRequest
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
        $type = $entity = null;

        if(strpos($this->url(),"taches") !== false){
            $entity = Optional(Tache::findByKey($this->id))->activite;
            $type = 'tache';
        }
        else if(strpos($this->url(),"activites") !== false){
            $entity = optional(Activite::findByKey($this->id))->projet;
            $type = 'activite';           
        }
        
        return [
            'debut' => [
                "required",
                "date",
                "date_format:Y-m-d",
                function($attribute, $value, $fail) use ($entity, $type) {
                    if ($entity && $type == "tache" && $entity->dureeActivite->debut > $value) { // Check if $duree is set
                        $fail("La date de debut de la tache est antérieure à celle de l'activité");
                    }

                    else if ($entity && $type == "activite" && $entity->debut > $value) { // Check if $duree is set
                        $fail("La date de debut de l'activité est antérieure à celle du projet");
                    }
                    else if ($entity == null || $type == null) { // Check if $duree is set
                        $fail("Fail ");
                    }
                }
            ],
            'fin' => [
                "required",
                "date",
                "date_format:Y-m-d",
                "after_or_equal:debut",
                function($attribute, $value, $fail) use ($entity, $type) {
                    if ($entity && $type == "tache" && $entity->dureeActivite->fin < $value) { // Check if $duree is set
                        $fail("La date de fin de la tache est supérieure à celle de l'activité");
                    }

                    else if ($entity && $type == "activite" && $entity->fin < $value) { // Check if $duree is set
                        $fail("La date de fin de l'activité est supérieure à celle du projet");
                    }
                    else if ($entity == null || $type == null) { // Check if $duree is set
                        $fail("Fail ");
                    }
                }
            ]
        ];
    }
}
