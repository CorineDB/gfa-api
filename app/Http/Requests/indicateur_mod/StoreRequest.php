<?php

namespace App\Http\Requests\indicateur_mod;

use App\Models\MOD;
use App\Models\Categorie;
use App\Models\Unitee;
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
            'nom'               => 'required|max:255',

            'description'       => 'required',

            'anneeDeBase'       => 'required|date_format:Y|before_or_equal:'.now()->format("Y"),

            'valeurDeBase'      => 'required',

            'frequence'         => 'required|string',

            'source'            => 'required|string',

            'responsable'       => 'required|string',

            'definition'        => 'required|string',

            'uniteeMesureId'    => ['required', Rule::exists('unitees', 'id')->whereNull('deleted_at')],

            //'unitees_mesure'  => 'required|array',

            'categorieId'       => ['nullable', Rule::exists('categories', 'id')->whereNull('deleted_at')],

            'modId'             => [Rule::requiredIf(request()->user()->hasRole(['unitee-de-gestion'])), Rule::exists('mods', 'id')->whereNull('deleted_at')]

        ];
    }


    protected function prepareForValidation(): void
    {

        if(isset($this->categorieId))
        {
            $categorie = Categorie::decodeKey($this->categorieId);

            if(!$categorie)
                throw ValidationException::withMessages(['categorieId' => "Catégorie inconnue"]);

            $this->merge([
                'categorieId' => $categorie
            ]);
        }

        if(isset($this->modId))
        {
            $mod = MOD::decodeKey($this->modId);

            if(!$mod)
                throw ValidationException::withMessages(['modId' => "Mod inconnue"]);


            $this->merge([
                'modId' => $mod
            ]);
        }

        $uniteeMesure = Unitee::decodeKey($this->uniteeMesureId);

        if(!$uniteeMesure)
            throw ValidationException::withMessages(['uniteeMesureId' => "Unitee de mésure inconnue"]);


        $this->merge([
            'uniteeMesureId' => $uniteeMesure
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
            'nom.required'          => 'Le nom de l\'indicateur est obligatoire.',
            'description.required'  => 'La description de l\'indicateur est obligatoire.',
            'anneeDeBase.required'  => 'L\'annee de base est obligatoire.',
            'valeurDeBase.required' => 'La valeur de base est obligatoire.',
            'frequence.required'    => 'La frequence de suvie de l\'indicateur est requis.',
            'source.required'       => 'La source des informations est requis.',
            'responsable.required'  => 'Le responsable suivi de l\'indicateur est requis.',
            'definition.required'   => 'La definition de l\'indicateur est requis.',
            'unites.required'       => 'Veuillez préciser les unites de mésure de l\'indicateur',
            'unites.array'          => 'Veuillez préciser les unites de mesure de l\'indicateur dans un tableau',
            'categorieId.exists'    => 'Catégorie inconnu. Veuillez sélectionner une catégorie existant dans le système',
            'modId.required'        => 'Veuillez préciser le mod.',
            'modId.exists'          => 'Mod inconnu. Veuillez sélectionner un mod existant dans le système'
        ];
    }
}
