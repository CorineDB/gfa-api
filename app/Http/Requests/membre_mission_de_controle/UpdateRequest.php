<?php

namespace App\Http\Requests\membre_mission_de_controle;

use App\Models\MissionDeControle;
use App\Models\User;
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

        return $user->hasRole("mission-de-controle", "unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $mission_de_controle = MissionDeControle::decodeKey($this->id);

        if(!$mission_de_controle) throw ValidationException::withMessages(['message' => "Mission de controle introuvable"]);

        $membre_mission_de_controle = User::findByKey($this->membre);

        if(!$membre_mission_de_controle) throw ValidationException::withMessages(['message' => "Membre inconnu de la mission de controle"]);      

        return [
            'nom'       => ['required','max:255', Rule::unique('users')->ignore($membre_mission_de_controle)->whereNull('deleted_at')],
            'prenom'    => ['required','max:255', Rule::unique('users')->ignore($membre_mission_de_controle)->whereNull('deleted_at')],
            'contact'   => ['required','max:255', Rule::unique('users')->ignore($membre_mission_de_controle)->whereNull('deleted_at')],
            'email'     => ['required','email','max:255', Rule::unique('users')->ignore($membre_mission_de_controle)->whereNull('deleted_at')],
            'poste'     => ['required','max:255']
        ];
    }
}
