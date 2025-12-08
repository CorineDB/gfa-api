<?php

namespace App\Http\Resources\anos;

use App\Http\Resources\CommentaireResource;
use App\Http\Resources\fichiers\FichiersResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AnosResource extends JsonResource
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
            "id"                    => $this->secure_id,
            "dossier"               => $this->dossier,
            "destinataire"          => $this->destinataire,
            "dateDeSoumission"      => $this->dateDeSoumission,
            "dateDeReponse"         => $this->dateDeReponse,
            "statut"                => $this->statut,
            "auteur"                => ! $this->auteur ? null : [

                "id" => $this->auteur->secure_id,
                "nom" => $this->auteur->nom,
                "prenom" =>  $this->auteur->prenom
            ],
            "fichiers" => FichiersResource::collection($this->fichiers),
            "typeAno"               => $this->typeAno,
            "bailleur"              => ! $this->bailleur ? null : [
                "id"                => $this->bailleur->secure_id,
                "sigle"             => $this->bailleur->sigle,
                "user"              =>[
                        "id" => $this->bailleur->user->secure_id,
                        "nom" => $this->bailleur->user->nom,
                    ]
            ],
            "commentaires" => CommentaireResource::collection($this->commentaires),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
