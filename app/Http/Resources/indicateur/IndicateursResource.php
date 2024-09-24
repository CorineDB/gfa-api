<?php

namespace App\Http\Resources\indicateur;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateursResource extends JsonResource
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
            "categorie" => $this->categorie,
            "uniteeDeMesure" => $this->unitee_mesure,
            "bailleur" => [
                "id" => $this->bailleur->secure_id,
                "sigle" => $this->bailleur->sigle,
                "user" =>
                    [
                        "id" => $this->bailleur->user->secure_id,
                        "nom" => $this->bailleur->user->nom,
                    ]
            ],
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
