<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorieResource extends JsonResource
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
        ];
    }
}
