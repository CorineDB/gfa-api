<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationsEnqueteResource extends JsonResource
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
            "id"                    => $this->secure_id,
            'nom'                   => optional($this->user)->nom ?? null,
            'sigle'                 => $this->when($this->sigle, $this->sigle),
            'code'                  => $this->when($this->code, $this->code),
            'nom_point_focal'       => $this->nom_point_focal,
            'prenom_point_focal'    => $this->prenom_point_focal,
            'contact_point_focal'   => $this->contact_point_focal
        ];
    }
}
