<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TacheResource extends JsonResource
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
            "codePta" => $this->codePta,
            "poids" => $this->poids,
            "debut" => $this->debut,
            "fin" => $this->fin,
            "poidsActuel" => optional($this->suivis->last())->poidsActuel ?? 0,
            "position" => $this->position,
            "description" => $this->description,
            "durees" => $this->durees,
            "statut" => $this->statut,
            "activiteId" => optional($this->activite)->secure_id,

            $this->mergeWhen(optional($this->activite)->composante->composanteId === 0, function(){
                return [
                    "bailleur" => optional(optional($this->activite->composante)->projet)->bailleur === null ? null : [
                        "id" => $this->activite->composante->projet->bailleur->id,
                        "sigle" => $this->activite->composante->projet->bailleur->sigle,
                        "nom" => $this->activite->composante->projet->bailleur->user->nom
                    ]
                ];
            }),

            $this->mergeWhen(optional($this->activite)->composante->composanteId !== 0, function(){
                return [
                    "bailleur" => optional(optional(optional(optional($this->activite)->composante)->composante)->projet)->bailleur === null ? null : [
                        "id" => $this->activite->composante->composante->projet->bailleur->id,
                        "sigle" => $this->activite->composante->composante->projet->bailleur->sigle,
                        "nom" => $this->activite->composante->composante->projet->bailleur->user->nom
                    ]
                ];
            }),
            "tep" => $this->tep
        ];
    }
}
