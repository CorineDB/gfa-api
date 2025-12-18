<?php

namespace App\Http\Resources;

use App\Http\Resources\user\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationResource extends JsonResource
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
            'type'                  => $this->type,
            'nom_point_focal'       => $this->nom_point_focal,
            'prenom_point_focal'    => $this->prenom_point_focal,
            'contact_point_focal'   => $this->contact_point_focal,
            'longitude'             => $this->longitude,
            'latitude'              => $this->latitude,
            'addresse'              => $this->addresse,
            'quartier'              => $this->quartier,
            'arrondissement'        => $this->arrondissement,
            'commune'               => $this->commune,
            'departement'           => $this->departement,
            'pays'                  => $this->pays,
            'secteurActivite'       => $this->secteurActivite,
            'fondId'                => $this->fonds->first() ? $this->fonds->first()->secure_id : null,
            'fonds'                 => $this->fonds->map(fn($fond) => [
                'id' => $fond->secure_id,
                'nom_du_fond' => $fond->nom_du_fond,
                'fondDisponible' => $fond->fondDisponible,
                'budgetAllouer' => $fond->pivot->budgetAllouer
            ]),
            'user'                  => $this->whenLoaded('user', new UserResource($this->user)),
            'projet'                => $this->whenLoaded("projet", new ProjetsResource($this->projet)),
            "created_at"            => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
