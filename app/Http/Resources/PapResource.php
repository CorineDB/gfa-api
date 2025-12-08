<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PapResource extends JsonResource
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
            "nom" =>$this->nom,
            "contact" => $this->contact,
            "referencePieceIdentite" => $this->referencePieceIdentite,
            "sexe" => $this->sexe,
            "rue" => $this->rue,
            "site" => $this->site->nom,
            "bailleur" => $this->site->bailleurs->first()->sigle,
            "statut" => $this->statut,
            "modeDePaiement" => $this->modeDePaiement,
            "longitude" => $this->longitude,
            "latitude" => $this->latitude,
            "montant" => $this->montant,
            "payer" => $this->payer,
            "dateDePaiement" => $this->dateDePaiement,
            "dateDeCreation" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s"),
        ];
    }
}
