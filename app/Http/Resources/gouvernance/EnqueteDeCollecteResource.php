<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EnqueteDeCollecteResource extends JsonResource
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
            'nom' => $this->nom,
            'objectif' => $this->objectif,
            'description' => $this->description,
            'debut' => Carbon::parse($this->debut)->format("Y-m-d"),
            'fin' => Carbon::parse($this->fin)->format("Y-m-d"),
            'statut' => $this->statut,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
        ];
    }
}
