<?php

namespace App\Http\Resources\indicateur;

use App\Http\Resources\UniteeMesureResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateurResource extends JsonResource
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
            "valeurCibleTotal" => $this->valeurCibleTotal,
            "kobo" => $this->kobo,
            "koboVersion" => $this->koboVersion,
            //"anneeDeBase" => Carbon::parse($this->anneeDeBase)->format("Y"),
            "anneeDeBase" => $this->anneeDeBase,
            "valeurDeBase" => $this->valeurDeBase,
            "categorie" => $this->categorie ? [
                "id" => $this->categorie->secure_id,
                "nom" => $this->categorie->nom
            ] : null,
            "bailleur" => [
                "id" => $this->bailleur->secure_id,
                "nom" => $this->bailleur->user->nom
            ],
            "unitee_mesure" => [
                "id" => $this->unitee_mesure->secure_id,
                "nom" => $this->unitee_mesure->nom
            ],
            //"unitees_mesure" => UniteeMesureResource::collection($this->unitees_mesure),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
