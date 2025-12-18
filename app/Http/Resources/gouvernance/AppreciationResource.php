<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class AppreciationResource extends JsonResource
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
            'id'            => $this->secure_id,
            'organisation'  => [
                'id' => $this->organisation->secure_id,
                'sigle' => $this->organisation->sigle,
                'nom' => optional($this->organisation->user)->nom ?? null
            ],
            'user' => [
                'id' => $this->user->secure_id,
                'nom' => optional($this->user)->nom ?? null
            ],
            'contenu' => $this->contenu,
            "type" => $this->type,
            "enqueteDeCollecteId" => $this->enquete->secure_id,
        ];
    }
}
