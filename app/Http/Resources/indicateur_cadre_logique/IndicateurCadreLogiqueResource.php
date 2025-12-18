<?php

namespace App\Http\Resources\indicateur_cadre_logique;

use Illuminate\Http\Resources\Json\JsonResource;

class IndicateurCadreLogiqueResource extends JsonResource
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
            "nom"                   => $this->nom,
            "hypothèse"             => $this->hypothèse,
            "sourceDeVerification"  => $this->sourceDeVerification,
            "indicatable"           => !$this->indicatable ? null : [
                "id"                    => $this->indicatable->secure_id,
                "nom"                   => $this->indicatable->nom,
                "created_at"            => $this->indicatable->created_at
            ],           
            "created_at"            => $this->created_at
        ];
    }
}
