<?php

namespace App\Http\Resources;

use App\Http\Resources\FichierResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjetsResource extends JsonResource
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
            "couleur" => $this->couleur,
            "description" => $this->description,
            "pays" => $this->pays,/* 
            "commune" => $this->commune,
            "departement" => $this->departement,
            "arrondissement" => $this->arrondissement,
            "quartier" => $this->quartier, */
            //"bailleur" => $this->bailleur,
            "statut" => $this->statut,
            "image" => new FichierResource($this->image()),
            "link" => $this->chemin,// new FichiersResource($this->chemin),
            "budgetNational" => $this->budgetNational ?? 0,
            "pret" => $this->pret ?? 0,
            "depenses" => $this->consommer,
            "objectifGlobaux" => $this->objectifGlobaux,
            "tauxEngagement" => $this->tauxEngagement,
            "debut" => $this->debut,
            "fin" => $this->fin,
            "tep" => round($this->tep, 2),
        ];
    }
}
