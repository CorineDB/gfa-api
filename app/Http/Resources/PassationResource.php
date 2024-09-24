<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PassationResource extends JsonResource
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
            "montant" => $this->montant,
            "dateDeSignature" => $this->dateDeSignature,
            "dateDobtention" => $this->dateDobtention,
            "dateDobtentionAvance" => $this->dateDobtentionAvance,
            "dateDeDemarrage" => $this->dateDeDemarrage,
            "datePrevisionnel" => $this->datePrevisionnel,
            "montantAvance" => $this->montantAvance,
            "site" => $this->site->nom,
            "ordreDeService" => $this->ordreDeService,
            "responsableSociologue" => $this->responsableSociologue,
            "estimation" => $this->estimation,
            "travaux" => $this->travaux,
            "entreprise" => $this->entrepriseExecutant->user->nom,
            "passationable" => $this->passationable->user->nom,
            "dateDeCreation" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s"),
            "commentaires" => $this->commentaires
        ];
    }
}
