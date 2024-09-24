<?php

namespace App\Http\Resources\reponseAnos;

use App\Http\Resources\fichiers\FichiersResource;
use App\Http\Resources\user\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReponseAnoResource extends JsonResource
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
            "commentaire" => $this->commentaire,
            "statut" => $this->ano->statut,
            "anoId" => $this->ano->secure_id,
            "auteur" => new UserResource($this->auteur),
            "reponses" => $this->reponses,
            "reponse" => $this->reponse,
            "ano" => $this->ano === null ? null : [
                "id" => $this->ano->secure_id,
                "dossier" => $this->ano->dossier,
                "destinataire" => $this->ano->destinataire,
                "dateReponse" => Carbon::parse($this->ano->dateReponse)->format("Y-m-d"),
                "dateDeSoumission" => Carbon::parse($this->ano->dateDeSoumission)->format("Y-m-d"),
                'auteur' => $this->ano->auteur === null ? null : [
                    "id" => $this->ano->auteur->secure_id,
                    "nom" => $this->ano->auteur->nom
                ],
                'type' => $this->typeAno === null ? null : [
                    "id" => $this->typeAno->id,
                    "nom" => $this->typeAno->user->nom
                ]
            ],
            "bailleur" => $this->ano->bailleur === null ? null : [
                "id" => $this->ano->bailleur->secure_id,
                "sigle" => $this->ano->bailleur->sigle,
                "nom" => $this->ano->bailleur->user->nom
            ],
            "documents" => FichiersResource::collection($this->documents),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
