<?php

namespace App\Http\Resources\indicateur;

use App\Http\Resources\UniteeMesureResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateursValueKeyResource extends JsonResource
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
            "libelle" => $this->libelle,
            "key" => $this->key,
            "description" => $this->description,
            "type" => $this->when($this->uniteeMesure, $this->uniteeMesure->nom),
            "uniteeMesureId" => $this->when($this->uniteeMesure, $this->uniteeMesure->secure_id)
        ];
    }
}
