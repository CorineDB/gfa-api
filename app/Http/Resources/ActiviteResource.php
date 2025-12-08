<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ComposanteResource;
use Carbon\Carbon;

class ActiviteResource extends JsonResource
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
            "pret" => $this->pret,
            "position" => $this->position,
            "budgetNational" => $this->budgetNational,
            "depenses" => $this->consommer,
            "description" => $this->description,
            "statut" => $this->statut,
            "tep" => $this->tep,
            "tef" =>$this->tef(),
            "responsable" => $this->when($this->responsable, $this->responsable),
            "durees" => $this->durees,
            "composanteId" => optional($this->composante)->secure_id ?? 0 ,
            //"structureResponsable" => $this->structureResponsable(),
            //"structureAssociee" => $this->structureAssociee(),

            $this->mergeWhen($this->composante->composanteId === 0, function(){
                return [
                    "projet_owner" => optional(optional($this->composante)->projet)->projetable === null ? null : [
                        "id" => $this->composante->projet->projetable->id,
                        "sigle" => $this->when($this->composante->projet->projetable->sigle, $this->composante->projet->projetable->sigle),
                        "code" => $this->when($this->composante->projet->projetable->code, $this->composante->projet->projetable->code),
                        
                        "nom" => $this->composante->projet->projetable->user->nom
                    ]
                ];
            }),

            $this->mergeWhen($this->composante->composanteId !== 0, function(){
                return [
                    "projet_owner" => optional(optional($this->composante->composante)->projet)->projetable === null ? null : [
                        "id" => $this->composante->composante->projet->projetable->id,
                        "sigle" => $this->when($this->composante->composante->projet->projetable->sigle, $this->composante->composante->projet->projetable->sigle),
                        "code" => $this->when($this->composante->composante->projet->projetable->code, $this->composante->composante->projet->projetable->code),
                        "nom" => $this->composante->composante->projet->projetable->user->nom
                    ],
                ];
            }),

            $this->mergeWhen($this->composante->composanteId === 0, function(){
                return [
                    "bailleur" => optional(optional($this->composante)->projet)->bailleur === null ? null : [
                        "id" => $this->composante->projet->bailleur->id,
                        "sigle" => $this->composante->projet->bailleur->sigle,
                        "nom" => $this->composante->projet->bailleur->user->nom
                    ]
                ];
            }),

            $this->mergeWhen($this->composante->composanteId !== 0, function(){
                return [
                    "bailleur" => optional(optional($this->composante->composante)->projet)->bailleur === null ? null : [
                        "id" => $this->composante->composante->projet->bailleur->id,
                        "sigle" => $this->composante->composante->projet->bailleur->sigle,
                        "nom" => $this->composante->composante->projet->bailleur->user->nom
                    ],
                ];
            }),

            /*
                "debut" => $this->duree->debut,
                "fin" => $this->duree->fin,
            */

            "debut" => $this->duree === null ? null : $this->duree->debut,
            "fin" => $this->duree === null ? null : $this->duree->fin,
            "taches" => TacheResource::collection($this->taches),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
