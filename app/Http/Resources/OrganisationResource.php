<?php

namespace App\Http\Resources;

use App\Http\Resources\FichierResource;
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
            "id" => $this->id,
            'nom' => $this->whenLoaded('user', $this->user->nom),
            'sigle' => $this->when($this->sigle, $this->sigle),
            'code' => $this->when($this->code, $this->code),
            'user' => $this->whenLoaded('user', new UserResource($this->user)),
            //"user" => new UserResource($this->user),
            'projet' => $this->whenLoaded("projet", $this->projet),
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
