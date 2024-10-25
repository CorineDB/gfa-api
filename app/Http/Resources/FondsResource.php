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
            'libelle' => $this->libelle,
            'description' => $this->description,
            'type' => $this->type,
            'lien' => $this->lien,
            'annee_exercice' => $this->annee_exercice,
            'created_by' => $this->created_by->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => $this->created_at
        ];
    }
}
