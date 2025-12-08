<?php

namespace App\Http\Requests\esuivie;

use App\Models\CheckList;
use App\Models\EActivite;
use App\Models\EntrepriseExecutant;
use App\Models\User;
use App\Models\Formulaire;
use App\Models\Site;
use App\Rules\HashValidatorRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
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

            'activiteStatut'        => 'required|integer|min:0|max:1',

            'commentaire'          => 'sometimes',

            'checkListId' => ['required', new HashValidatorRule(new CheckList())],

            'activiteId' => ['required', new HashValidatorRule(new EActivite())],

            'entrepriseExecutantId' => ['required', new HashValidatorRule(new EntrepriseExecutant())],

            'userId' => ['required', new HashValidatorRule(new User())],

            'siteId' => ['required', new HashValidatorRule(new Site())],

            'formulaireId' => ['required', new HashValidatorRule(new Formulaire())],

        ];
    }
}
