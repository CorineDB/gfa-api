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

class StoreRequest extends FormRequest
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

        return [
            'valeur'                => 'required|max:255',

            'responsable_enquete'   => 'required|max:255',

            'mois'                  => ["bail",'required', 'integer', 'date_format:m', 'min:1', 'max:12', Rule::unique('com_suivis','mois')->where("checkListComId", $this->checkListComId)->where("annee", $this->annee)->whereNull('deleted_at'), 'size:'.$this->max],

            'annee'                 => 'required|integer|date_format:Y|size:'.now()->format('Y'),

            'checkListComId'        => ['required', Rule::exists('check_list_com', 'id')->whereNull('deleted_at')],

        ];
    }

    protected function prepareForValidation(): void
    {
            
        $checkListCom = CheckListCom::decodeKey($this->checkListComId);

        if(!$checkListCom)
            throw ValidationException::withMessages(['checkListComId' => "Check list inconnue"]);
            

        $this->merge([
            'checkListComId' => $checkListCom
        ]);

        $count = SuiviCheckListCom::where(['checkListComId' => $this->checkListComId, 'annee' => $this->annee, 'deleted_at' => NULL])->get()->count();


        $count = $count >= 12 ? 12 : $count + 1;

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
            'mois.size'                     => 'Le suivi du mois de '. Carbon::createFromFormat("m",$this->max)->format('F') . ' doit se faire avant ce mois',
            'mois.unique'                   => 'Le suivi de cette check list pour ce mois avait déjà été éffectué.',
            'responsable_enquete.required'  => 'Veuillez préciser le responsable requete.',
            'checkListComId.required'       => 'Veuillez préciser l\'unitée de mésure.',
            'checkListComId.exists'         => 'Unitée de mésure inconnu. Veuillez sélectionner une unitée de mésure existant dans le système'
        ];
    }
}
