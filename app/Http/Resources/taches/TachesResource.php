<?php

namespace App\Http\Resources\taches;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TachesResource extends JsonResource
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
            "poidsActuel" => optional($this->suivis->last())->poidsActuel ?? 0,
            "position" => $this->position,
            "description" => $this->description,
            "durees" => $this->durees,
            "statut" => $this->statut,
            "activiteId" => $this->activite->secure_id,
            "bailleur" => optional(optional(optional($this->activite)->composante)->projet)->bailleur === null ? null : [
                "id" => $this->activite->composante->projet->bailleur->id,
                "sigle" => $this->activite->composante->projet->bailleur->sigle,
                "nom" => $this->activite->composante->projet->bailleur->user->nom
            ],
            "tep" => $this->tep,
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
