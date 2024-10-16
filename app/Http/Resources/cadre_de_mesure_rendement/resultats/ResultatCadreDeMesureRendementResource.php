<?php

namespace App\Http\Resources\cadre_de_mesure_rendement\resultats;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultatCadreDeMesureRendementResource extends JsonResource
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
            'id' => $this->secure_id,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at, 'Y-m-d H:m:s')
        ];
    }
}
