<?php

namespace App\Http\Requests\suivi_financier_mod;

use App\Models\MaitriseOeuvre;
use App\Models\Site;
use App\Models\SuiviFinancierMod;
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
        
        return $user->hasRole("unitee-de-gestion", "mod");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'siteId'                => ['required', 'int', Rule::exists('sites','id')->whereNull('deleted_at')],
            'trimestre'             => ["bail",'required', 'integer', 'min:1', 'max:4', Rule::unique('suivi_financier_mods','trimestre')->where("siteId", $this->siteId)->where("annee", $this->annee)->whereNull('deleted_at'), 'size:'.$this->max],
            'annee'                 => ['required', 'date_format:Y','size:'.now()->format('Y')],
            'decaissement'          => 'required|integer',
            'taux'                  => 'required|integer',
            'commentaire'          => 'sometimes',
            'maitriseDoeuvreId'     => ['required', 'int', Rule::exists('maitrise_oeuvres','id')->whereNull('deleted_at')],
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
            'trimestre.required'            => 'Veuillez préciser le trimestre pour lequel le suivi se fera.',
            'trimestre.min'                 => 'La valeur minimal pour le trimestre est 1',
            'trimestre.max'                 => 'Vous ne pouvez que faire le suivi de 4 trimestre pour l\'année : ' . $this->annee,
            'trimestre.size'                => 'Le suivi ne peut que se faire pour le trimestre '. $this->max,
            'trimestre.unique'              => 'Le suivi financier pour ce trimestre avait déjà été éffectué.',
            'annee.required'                => 'Veuillez préciser l\'année du suivi.',
            'decaissement.required'         => 'Veuillez préciser le montant du décaissement.',
            'taux.required'                 => 'Veuillez le taux.',
            'commentaire.required'          => 'Veuillez laisser un commentaire concernant ce suivi.',
            'siteId.required'               => 'Veuillez préciser le site auquelle sera associé le suivi .',
            'siteId.exists'                 => 'Site inconnu. Veuillez préciser un site existant.',
            'maitriseDoeuvreId.required'    => 'Veuillez préciser la maitrise d\'oeuvre .',
            'maitriseDoeuvreId.exists'      => 'Maitrise d\'oeuvre inconnu. Veuillez préciser la maitrise d\'oeuvre site existant.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $site = Site::decodeKey($this->siteId);

        if(!$site)
            throw ValidationException::withMessages(['siteId' => "Site introuvable"]);
            
        $this->merge([
            'siteId' => $site
        ]);

        $maitriseDoeuvre = MaitriseOeuvre::decodeKey($this->maitriseDoeuvreId);

        if(!$maitriseDoeuvre)
            throw ValidationException::withMessages(['maitriseDoeuvreId' => "Maitrise d'oeuvre inconnue"]);
            
        $this->merge([
            'maitriseDoeuvreId' => $maitriseDoeuvre
        ]);

        $count = SuiviFinancierMod::where(['siteId' => $this->siteId, 'annee' => $this->annee, 'deleted_at' => NULL])->get()->count();

        $count = $count >= 4 ? 4 : $count + 1;

        $this->merge([
            'max' => $count
        ]);

    } 

}
