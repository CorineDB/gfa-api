<?php

namespace App\Http\Resources\fichiers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FichiersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return !$this->resource ? null : [
            "id" => $this->secure_id,
            "nom" => $this->nom,
            "url" => Storage::url($this->chemin)
        ];
    }
}
