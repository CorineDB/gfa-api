<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FondsResource extends JsonResource
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
            'nom_du_fond' => $this->nom_du_fond,
            'fondDisponible' => $this->fondDisponible
        ];
    }
}
