<?php

namespace App\Http\Resources\mission_de_controles;

use App\Http\Resources\bailleurs\BailleurResource;
use App\Http\Resources\programmes\ProgrammeResource;
use App\Http\Resources\user\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MissionDeControlesResource extends JsonResource
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
            "user" => new UserResource($this->whenLoaded('user')),
            "bailleur" => new BailleurResource($this->bailleur),
            "programme" => new ProgrammeResource($this->whenLoaded('programme')),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
