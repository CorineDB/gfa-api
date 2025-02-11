<?php

namespace App\Http\Resources\user\auth;

use App\Http\Resources\fichiers\FichiersResource;
use App\Http\Resources\programmes\ProgrammesResource;
use App\Http\Resources\ProjetResource;
use App\Http\Resources\role\RoleResource;
use App\Models\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AuthResource extends JsonResource
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
            "email" => $this->email,
            "contact" => $this->contact,
            "type" => $this->type,
            "profil" => $this->when($this->type != 'administrateur', function(){
                return $this->profilable;
            }),
            "programme" => $this->when($this->type !== 'administrateur', $this->programme),
            "role" => RoleResource::collection($this->roles->load('permissions')),
            "photo" => new FichiersResource($this->photo),
            "projet" => $this->when((($this->type == 'organisation') || $this->profilable_type == Organisation::class), function(){
                return $this->profilable->projet;
            })
        ];
    }
}
