<?php

namespace App\Http\Resources\cadre_de_mesure_rendement;

use App\Http\Resources\indicateur\IndicateurResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MesureRendementProjetResource extends JsonResource
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
            "type" => $this->type,
            "indice" => $this->indice,
            "code" => $this->code,
            "categorieId" => $this->categorie ? $this->categorie->secure_id : null,
            "programmeId" => $this->programme ? $this->programme->secure_id : null,
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d"),
            'categories' => MesureRendementProjetResource::collection($this->categories),
            'indicateurs' => IndicateurResource::collection($this->indicateurs)
        ];

        /* $indice = $this->pivot->position;
        return [
            'id' => $this->secure_id,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'type' => $this->pivot->type,
            'position' => $this->pivot->position,
            // Pass the external value when creating each resource in the collection
            'indicateurs' => (CadreDeMesureRendement::find($this->pivot->id)->mesures)->map(function ($mesure) use ($indice) {
                return new IndicateurResource($mesure, $indice); // Pass external value here
            }),
            //'indicateurs' => IndicateurResource::collection($this->resultats_de_mesure_rendement),
            'created_at' => Carbon::parse($this->created_at, 'Y-m-d H:m:s')
        ]; */
    }
}
