<?php

namespace App\Http\Resources\indicateur;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->secure_id,
            "nom" => $this->nom,
            "description" => $this->description,
            "kobo" => $this->kobo,
            "koboVersion" => $this->koboVersion,
            //"anneeDeBase" => Carbon::parse($this->anneeDeBase)->format("Y"),
            "categorie" => $this->categorie ? [
                "id" => $this->categorie->secure_id,
                "nom" => $this->categorie->nom
            ] : null,
            "agreger" => $this->agreger,
            "value_keys" => IndicateurValueKeyResource::collection($this->valueKeys),
            "unitee_mesure" => $this->when($this->unitee_mesure, [
                "id" => $this->unitee_mesure->secure_id,
                "nom" => $this->unitee_mesure->nom
            ]),
            "anneeDeBase" => $this->anneeDeBase,
            "valeurDeBase" => $this->valeurDeBase,
            "valeursCible" => $this->valeursCible ? $this->valeursCible->map(function($valeurCible){
                return [
                    "id" => $valeurCible->secure_id,
                    "annee" => $valeurCible->annee,
                    "valeurCible" => $valeurCible->valeurCible,
                    "valeur_realiser" => $valeurCible->valeur_realiser
                ];
            }) : null,
            "valeurCibleTotal" => $this->valeurCibleTotal(),
            "valeurRealiserTotal" => $this->valeurRealiserTotal(),
            "taux_realisation" => $this->taux_realisation,
            /*"bailleur" => [
                "id" => $this->bailleur->secure_id,
                "sigle" => $this->bailleur->sigle,
                "user" =>
                    [
                        "id" => $this->bailleur->user->secure_id,
                        "nom" => $this->bailleur->user->nom,
                    ]
            ],*/
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
