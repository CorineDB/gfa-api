<?php

namespace App\Http\Resources;

use App\Http\Resources\FichierResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjetResource extends JsonResource
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
            "nombreEmploie" => $this->nombreEmploie,
            //"statistiqueActivite" => $this->statistiqueActivite(),
            "budgetNational" => $this->budgetNational ?? 0,
            "pret" => $this->pret ?? 0,
            "depenses" => $this->consommer,
            "objectifGlobaux" => $this->objectifGlobaux,
            "pays" => $this->pays,/* 
            "quartier" => $this->quartier,
            "commune" => $this->commune,
            "departement" => $this->departement,
            "arrondissement" => $this->arrondissement, */
            "tauxEngagement" => $this->tauxEngagement,
            "secteurActivite" => $this->secteurActivite,
            "dateAprobation" => $this->dateAprobation,
            "description" => $this->description,
            //"bailleur" => $this->bailleur,
            "owner" => $this->projetable,
            "statut" => $this->statut,
            "tep" => round($this->tep, 2),
            "tef" => round($this->tef, 2),
            "tepByAnnee" => round($this->tepByAnnee, 2),
            "image" => new FichierResource($this->image()),
            "fichiers" => FichierResource::collection($this->allFichiers()->where('sharedId', null)),
            //"tauxDecaissementParAnnee" => $this->tauxDeDecaissementParAnnee(),
            //"tauxDecaissementAnneeEnCours" => $this->tauxDeDecaissementAnneeEnCours(),
            //"tefParAnnee" => $this->tefParAnnee(),
            "programme" => $this->programme,
            "link" => $this->chemin,// new FichiersResource($this->chemin),
            "composantes" => ComposanteResource::collection($this->composantes),
            "sites" => SiteResource::collection($this->sites),
            "audit" => $this->audits->last()
        ];
    }
}
