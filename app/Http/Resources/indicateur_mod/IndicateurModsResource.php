<?php

namespace App\Http\Resources\indicateur_mod;

use Illuminate\Http\Resources\Json\JsonResource;

class IndicateurModsResource extends JsonResource
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
            "description" => $this->description,
            "anneeDeBase" => $this->anneeDeBase,
            "valeurDeBase" => $this->valeurDeBase,
            "categorieId" => $this->categorieId,      
            "created_at" => $this->created_at
        ];
    }
}
