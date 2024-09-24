<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ActiviteResource;
use Carbon\Carbon;

class ComposanteResource extends JsonResource
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
                "pret" => $this->pret,
                "budgetNational" => $this->budgetNational,
                "description" => $this->description,
                "statut" => $this->statut,
                "tep" => $this->tep,

                $this->mergeWhen($this->projet, function(){
                    return ["sigle" => $this->projet->bailleur->sigle];
                }),

                $this->mergeWhen($this->composante, function(){
                    return ["sigle" => $this->composante->projet->bailleur->sigle];
                }),
                "projetId" => optional($this->projet)->secure_id,
                "composanteId" => optional($this->composante)->secure_id,
                "position" => $this->position,
                  "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
            ];
    }
}
