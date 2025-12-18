<?php

namespace App\Http\Requests\suivi_check_list_com;

use App\Models\CheckListCom;
use App\Models\SuiviCheckListCom;
use App\Models\Unitee;
use Carbon\Carbon;
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

        return $user->hasRole("ong", "agence");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if(is_string($this->suivi_checks_list_ongs_agence))
        {
            $this->suivi_checks_list_ongs_agence = SuiviCheckListCom::findByKey($this->suivi_checks_list_ongs_agence);
        }

        $rules = [

            'valeur'                => 'required|max:255',

            'responsable_enquete'   => 'required|max:255',

            'annee'                 => 'required|integer|date_format:Y|size:'.now()->format('Y'),

            'mois'                  => ["bail",'required', 'integer', 'date_format:m', 'min:1', 'max:12', Rule::unique('com_suivis','mois')->ignore($this->suivi_checks_list_ongs_agence)->where("checkListComId", $this->checkListComId)->where("annee", $this->annee)->whereNull('deleted_at'), 'size:'.$this->max],

            'checkListComId'               => ['required', Rule::exists('check_list_com', 'id')->whereNull('deleted_at')],

        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if(is_string($this->suivi_checks_list_ongs_agence))
        {
            $this->suivi_checks_list_ongs_agence = SuiviCheckListCom::findByKey($this->suivi_checks_list_ongs_agence);
        }

        $checkListCom = CheckListCom::decodeKey($this->checkListComId);

        if(!$checkListCom)
            throw ValidationException::withMessages(['checkListComId' => "Check list inconnue"]);
            

        $this->merge([
            'checkListComId' => $checkListCom
        ]);

        if($this->annee ==  $this->suivi_checks_list_ongs_agence->annee)
            $count = $this->suivi_checks_list_ongs_agence->mois;
        else
        {
            $count = SuiviCheckListCom::where(['checkListComId' => $this->checkListComId, 'annee' => $this->annee, 'deleted_at' => NULL])->get()->count();

            $count = $count >= 12 ? 12 : $count + 1;
        }

        $this->merge([
            'max' => $count
        ]);
    } 

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'valeur.required'               => 'Veuillez préciser la valeur du suivi.',
            'annee.required'                => 'Veuillez préciser l\'année du suivi.',
            'mois.required'                 => 'Veuillez préciser le mois du suivi.',
            'mois.min'                      => 'La valeur minimal pour le mois est 1',
            'mois.max'                      => 'Vous ne pouvez que faire le suivi de 12 mois pour l\'année : ' . $this->annee,
            'mois.size'                     => 'Vous devriez plutôt faire le suivi du mois de '. Carbon::createFromFormat("m",$this->max)->format('F'),
            'mois.unique'                   => 'Le suivi de cette check list pour ce mois avait déjà été éffectué.',
            'responsable_enquete.required'  => 'Veuillez préciser le responsable requete.',
            'checkListComId.required'       => 'Veuillez préciser l\'unitée de mésure.',
            'checkListComId.exists'         => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système'
        ];
    }
}
