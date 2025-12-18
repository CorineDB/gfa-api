<?php

namespace App\Http\Resources;

use App\Http\Resources\FichierResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjetStatistiqueResource extends JsonResource
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
            "debut" => $this->debut,
            "fin" => $this->fin,
            "couleur" => $this->couleur,
            "pret" => $this->pret ?? 0,
            "nombreEmploie" => $this->nombreEmploie ?? 0,
            "statistiqueActivite" => $this->statistiqueActivite(),
            "budgetNational" => $this->budgetNational ?? 0,
            "pret" => $this->pret ?? 0,
            "objectifGlobaux" => $this->objectifGlobaux,
            "pays" => $this->pays,
            "sites" => SiteResource::collection($this->sites),/* 
            "commune" => $this->commune,
            "departement" => $this->departement,
            "arrondissement" => $this->arrondissement,
            "quartier" => $this->quartier, */
            "tauxEngagement" => $this->tauxEngagement,
            "secteurActivite" => $this->secteurActivite,
            "dateAprobation" => $this->dateAprobation,
            "description" => $this->description,
            "bailleur" => $this->bailleur,
            "statut" => $this->statut,
            "tep" => round($this->tep, 2),
            "tef" => round($this->tef, 2),
            "consommer" => round($this->consommer, 2),
            "tepByAnnee" => round($this->tepByAnnee, 2),
            "fichiers" => FichierResource::collection($this->allFichiers()->where('sharedId', null)),
            "tauxDecaissementParAnnee" => $this->tauxDeDecaissementParAnnee(),
            "tauxDecaissementAnneeEnCours" => $this->tauxDeDecaissementAnneeEnCours(),
            "tefParAnnee" => $this->tefParAnnee(),
            "audit" => $this->audits->last()
        ];
    }
}
