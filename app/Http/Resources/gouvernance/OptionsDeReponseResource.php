<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class OptionsDeReponseResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'programmeId' => $this->programme->secure_id,
            'created_at' => $this->created_at
        ];
    }
}
