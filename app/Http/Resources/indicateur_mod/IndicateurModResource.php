<?php

namespace App\Http\Resources\indicateur_mod;

use App\Http\Resources\UniteeMesureResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateurModResource extends JsonResource
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
            "frequence" => $this->frequence,
            "description" => $this->description,
            "anneeDeBase" => $this->anneeDeBase,
            "valeurDeBase" => $this->valeurDeBase,
            "categorie" => $this->categorie ? [
                "id" => $this->categorie->secure_id,
                "nom" => $this->categorie->nom
            ] : null,
            "mod" => [
                "id" => $this->mod->secure_id,
                "nom" => $this->mod->user ? $this->mod->user->nom : null
            ],
            "unitee_mesure" => $this->unitee_mesure ? [
                "id" => $this->unitee_mesure->secure_id,
                "nom" => $this->unitee_mesure->nom
            ] : null,
            //"unitees_mesure" => UniteeMesureResource::collection($this->unitees_mesure),
            "created_at" => $this->created_at
        ];
    }
}
