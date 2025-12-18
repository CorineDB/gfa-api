<?php

namespace App\Http\Resources\activites;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivitesResource extends JsonResource
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
            "type" => $this->type,
            "position" => $this->position,
            "budgetNational" => $this->budgetNational,
            "pret" => $this->pret,
            "description" => $this->description,
            "statut" => $this->statut,
            "tep" => $this->tep,
            "tef" => $this->tef(),
            "responsable" => $this->responsable,
            "durees" => $this->durees,
            "composanteId" => optional($this->composante)->secure_id,
            "structureResponsable" => $this->structureResponsable(),
            "structureAssociee" => $this->structureAssociee(),
            "bailleur" => optional(optional($this->composante)->projet)->bailleur === null ? null : [
                "id" => $this->composante->projet->bailleur->id,
                "sigle" => $this->composante->projet->bailleur->sigle,
                "nom" => $this->composante->projet->bailleur->user->nom
            ],
            "debut" => $this->duree === null ? null : $this->duree->debut,
            "fin" => $this->duree === null ? null : $this->duree->fin,
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
