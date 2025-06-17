<?php

namespace App\Http\Resources\indicateur;

use App\Http\Resources\OrganisationResource;
use App\Http\Resources\user\UniteeGestionResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateurResource extends JsonResource
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
            "indice" => $this->indice,
            "code" => $this->code,
            "kobo" => $this->kobo,
            "koboVersion" => $this->koboVersion,
            "categorieId" => $this->categorie ? $this->categorie->secure_id : null,
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
            })  : null,
            "valeurCibleTotal" => $this->valeurCibleTotal(),
            "valeurRealiserTotal" => $this->valeurRealiserTotal(),
            "taux_realisation" => $this->taux_realisation,
            /*"bailleur" => [
                "id" => $this->bailleur->secure_id,
                "nom" => $this->bailleur->user->nom
            ],*/
            "sources_de_donnee"         => $this->sources_de_donnee,
            "methode_de_la_collecte"    => $this->methode_de_la_collecte,
            "frequence_de_la_collecte"  => $this->frequence_de_la_collecte,
            "ug_responsable"            => new UniteeGestionResource($this->ug_responsable->first()),
            "organisations_responsable" => OrganisationResource::collection($this->organisations_responsable),
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
