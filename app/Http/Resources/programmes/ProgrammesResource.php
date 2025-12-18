<?php

namespace App\Http\Resources\programmes;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgrammesResource extends JsonResource
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
            "code" => $this->code,
            "description" => $this->description,
            "objectifGlobaux" => $this->objectifGlobaux,
            "budgetNational" => $this->budgetNational,
            "pret" => $this->pret,
            "organismeDeTutelle" => $this->organismeDeTutelle,
            "debut" => Carbon::parse($this->debut)->format("Y-m-d"),
            "fin" => Carbon::parse($this->fin)->format("Y-m-d"),
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
