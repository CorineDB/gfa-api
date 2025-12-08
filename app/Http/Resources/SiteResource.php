<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $entreprise = "";
        $entrepriseId = "";
        foreach($this->entreprises as $e)
        {
            $entreprise .= ' '.$e->user->nom . ',';

            $entrepriseId .= ' '.$e->secure_id . ',';
        }

        $entreprise = substr($entreprise, 0, -1);
        $entrepriseId = substr($entrepriseId, 0, -1);

        return [
            "id" => $this->secure_id,
            "nom" => $this->nom,
            "quartier" => $this->quartier,
            "arrondissement" => $this->arrondissement,
            "commune" => $this->commune,
            "departement" => $this->departement,
            "longitude" => $this->longitude,
            "latitude" => $this->latitude,/*
            "entreprise" => $entreprise,
            "bailleur" => $this->bailleurs->first()->sigle,
            "bailleurId" => $this->bailleurs()->first()->secure_id,
            "entrepriseId" => $this->entreprises*/
        ];
    }
}
