<?php

namespace App\Http\Requests\esuivie;

use App\Models\CheckList;
use App\Models\EActivite;
use App\Models\EntrepriseExecutant;
use App\Models\ESuivi;
use App\Models\MissionDeControle;
use App\Models\Site;
use App\Models\User;
use App\Rules\HashValidatorRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
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
        return true;
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

            'date'                  => 'required|date|date_format:Y-m-d',

            'activiteStatut'        => 'required|integer|min:3|max:4',

            'commentaire'          => 'sometimes',

            'checkListId' => ['required', new HashValidatorRule(new CheckList())],

            'activiteId' => ['required', new HashValidatorRule(new EActivite())],

            'entrepriseExecutantId' => ['required', new HashValidatorRule(new EntrepriseExecutant())],

            'userId' => ['required', new HashValidatorRule(new User())],
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
            'valeur.required'               => 'Veuillez préciser la valeur du suivi.',
            'entrepriseExecutantId.required'       => 'Veuillez préciser l\'entreprise executant.',
            'userId.exists'         => 'Responsable enqueté  inconnu. ',
            'userId.required'       => 'Veuillez préciser le responsable enqueté.',            'checkListId.required'       => 'Veuillez préciser la check list.',
            'checkListId.exists'       => 'Check list inconnue.',
        ];
    }
}
