<?php

namespace App\Http\Resources\indicateur_cadre_logique;

use Illuminate\Http\Resources\Json\JsonResource;

class IndicateursCadreLogiqueResource extends JsonResource
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
            "hypothèse" => $this->hypothèse,
            "sourceDeVerification" => $this->sourceDeVerification,
            "created_at" => $this->created_at
        ];
    }
}
