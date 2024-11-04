<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class IndicateursDeGouvernanceResource extends JsonResource
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
            'id'                        => $this->secure_id,
            'nom'                       => $this->when($this->nom, $this->nom),
            'description'               => $this->when($this->description, $this->description),
            'type'                      => $this->when($this->type, $this->type),
            'programmeId'               => $this->programme ? $this->programme->secure_id : null
        ];
    }
}
