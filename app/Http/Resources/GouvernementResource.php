<?php

namespace App\Http\Resources;

use App\Http\Resources\user\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class GouvernementResource extends JsonResource
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
            "programme" => $this->user->programme,
            "user" => new UserResource($this->user),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
