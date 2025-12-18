<?php

namespace App\Http\Resources;

use App\Http\Resources\bailleurs\BailleurResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MaitriseOeuvreResource extends JsonResource
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
            'estimation' => $this->estimation,
            'reference' => $this->reference,
            'bailleur' => new BailleurResource($this->bailleur),
            'commentaire' => $this->commentaires->first(),
        ];
    }
}
