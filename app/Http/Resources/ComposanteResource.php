<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ActiviteResource;
use App\Models\Code;
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
                "description" => $this->description,
                "statut" => $this->statut,
                "budgetNational" => $this->budgetNational,
                "pret" => $this->pret,
                "depenses" => $this->consommer,
                "tep" => $this->tep,

                $this->mergeWhen($this->projet && optional($this->projet->organisation), function(){
                    return ["sigle" => optional($this->projet->organisation)->sigle ?? null];
                }),

                $this->mergeWhen($this->composante && $this->composante->projet->organisation, function(){
                    return ["sigle" => optional($this->composante->projet->organisation)->sigle ?? null];
                }),
                "projetId" => optional($this->projet)->secure_id,
                "composanteId" => optional($this->composante)->secure_id,
                "souscomposantes" => ComposanteResource::collection($this->sousComposantes),
                "activites" => ActiviteResource::collection($this->activites),
                  //"souscomposantes" => !$this?->composanteId ? ComposanteResource::collection($this?->sousComposantes) : [],

                "position" => $this->position,
                  "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
            ];
    }
}
