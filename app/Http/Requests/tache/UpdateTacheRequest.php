<?php

namespace App\Http\Requests\tache;

use App\Models\Activite;
use App\Models\Tache;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

cLass UpdateTacheRequest extends FormRequest
{
    /**
     * Determine if the activite is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    /* {
        return request()->user()->hasPermissionTo("modifier-une-tache") || request()->user()->hasRole("unitee-de-gestion", "organisation");
    } */
    {
        $user = request()->user();

        // UG et Organisation avec permission peuvent modifier uniquement pour LEUR projet (projetable)
        if($user->hasPermissionTo("modifier-une-tache") && ($user->hasRole("organisation") || $user->hasRole("unitee-de-gestion"))) {
            $tache = $this->route('tach');

            if (!is_object($tache)) {
                if (!($tache = Tache::findByKey($tache))) {
                    throw ValidationException::withMessages(["tach" => "Tâche Inconnue"], 1);
                }
            }

            if($tache) {
                $activite = $tache->activite;
                $projet = $activite ? $activite->projet : null;

                // Vérifier si le projet appartient à l'utilisateur (organisation ou UG)
                if($projet) {
                    if($projet->projetable_type === 'App\Models\Organisation' && $user->hasRole("organisation")) {
                        return $projet->projetable_id === $user->profilable->id;
                    }
                    if($projet->projetable_type === 'App\Models\UniteeDeGestion' && $user->hasRole("unitee-de-gestion")) {
                        return $projet->projetable_id === $user->profilable->id;
                    }
                }
            }
        }

        return false;
    }

    public function prepareForValidation(){

        if(!is_object($this->tach))
        {
            if(!($tache = Tache::findByKey($this->tach))){
                throw ValidationException::withMessages(["tache" =>"Tâche Inconnue" ], 1);
            }

            $this->merge([
                "tache" => $tache->id
            ]);
        }
        else{
            $this->merge([
                "tache" => $this->tach->id
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => 'sometimes|required|max:255',
            'statut' => 'sometimes|required|integer|min:-1|max:-1',
            'poids' => ['nullable', 'numeric', 'min:0'],
            'activiteId' => ['sometimes', 'required', new HashValidatorRule(new Activite())],
            'debut' => ["sometimes", "required", "date", "date_format:Y-m-d", function($attribute, $value, $fail) {
                $activite = Activite::findByKey(request()->input('activiteId'));

                if ($activite && $value < $activite->dureeActivite->debut) {
                    $fail("La date de début de la tâche est antérieure à celle de l'activité.");
                }

            }],
            'fin' => ["sometimes", "required", "date", "date_format:Y-m-d", "after_or_equal:debut", function($attribute, $value, $fail) {
                $activite = Activite::findByKey(request()->input('activiteId'));

                    if ($activite && $value > $activite->dureeActivite->fin) {
                        $fail("La date de fin de la tâche est supérieure à celle de l'activité.");
                    }

            }],
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
            'nom.required' => 'Le nom de La tache est obligatoire.',
            'statut.required' => 'Le statut de la tache est obligatoire.',
            'poids.required' => 'Le poids de La tache est obligatoire.',
            'debut.required' => 'La date de debut de la tache est obligatoire.',
            'fin.required' => 'La date de fin de la tache est obligatoire.',
            'tepPrevu.required' => 'Le tep prévu de la tache est obligatoire.',
            'activiteId.required' => 'L\'activité est obligatoire'
        ];
    }
}
