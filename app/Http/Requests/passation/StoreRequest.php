<?php

namespace App\Http\Requests\passation;

use App\Models\MOD;
use App\Models\EntrepriseExecutant;
use App\Models\MissionDeControle;
use App\Models\Site;
use App\Rules\HashValidatorRule;
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
            'montant' => 'required|integer|min:0',
            'dateDeSignature' => 'date|date_format:Y-m-d',
            'dateDobtention' => 'date|date_format:Y-m-d',
            'dateDeDemarrage' => 'date|date_format:Y-m-d',
            'datePrevisionnel' => 'date|date_format:Y-m-d',
            'dateDobtentionAvance' => 'date|date_format:Y-m-d',
            'montantAvance' => 'required|integer|min:0',
            'ordreDeService' => 'required',
            'responsableSociologue' => 'required',
            'estimation' => 'required|integer|min:0',
            'travaux' => 'required',
            'entrepriseExecutantId' => ['required', new HashValidatorRule(new EntrepriseExecutant())],
            'siteId' => ['required', new HashValidatorRule(new Site())],
            'modId' => ['sometimes', 'required', new HashValidatorRule(new MOD())],
            'missionDeControleId' => ['sometimes','required', new HashValidatorRule(new MissionDeControle())],
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
            'montant.required' => 'Le montant est obligatoire',
            'montantAvance.required' => 'Le montant avancé est obligatoire',
            'ordreDeService.required' => 'L\'ordre de service est obligatoire',
            'responsableSociologue.required' => 'Le responsable sociologue est obligatoire',
            'estimation.required' => 'L\estimation est obligatoire',
            'siteId.required'       => 'Veuillez préciser le site.',
            'siteId.exists'         => 'Site inconnu. ',
            'entrepriseExecutantId.required'       => 'Veuillez préciser l\'entreprise executant.',
            'missionDeControleId.exists'         => 'Mission de controle inconnu. ',
            'missionDeControleId.required'       => 'Veuillez préciser la mission de controle.',
            'modId.required'       => 'Veuillez préciser le mod.',
            'modId.exists'       => 'Mod inconnue.',
        ];
    }
}
