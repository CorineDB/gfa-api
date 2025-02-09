<?php

namespace App\Http\Resources\user;

use App\Http\Resources\role\RoleResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
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
            "prenom" => $this->prenom,
            "contact" => $this->contact,
            "email" => $this->email,
            "type" => $this->type,
            "programmeId" => $this->programme->secure_id,
            "poste" => $this->poste,
            "roles" => RoleResource::collection($this->roles->load('permissions')),
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d"),
        ];
    }
}
