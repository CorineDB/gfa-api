<?php

namespace App\Http\Resources\user;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UniteeGestionResource extends JsonResource
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
            "programme" => $this->programme,
            "user" => new UserResource($this->user),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
