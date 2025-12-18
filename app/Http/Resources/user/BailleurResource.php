<?php

namespace App\Http\Resources\user;

use App\Http\Resources\ProjetResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BailleurResource extends JsonResource
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
            "code" => Auth::user()->hasRole("super-admin", "administrateur") ? 0 : $this->codes->where("programmeId", Auth::user()->programme->id)->first()->codePta,
            "sigle" => $this->sigle,
            "projet" => new ProjetResource(Auth::user()->hasRole("super-admin", "administrateur") ? null : $this->projets(Auth::user()->programme->id)),
            "pays" => $this->pays,
            "user" => new UserResource($this->whenLoaded('user')),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
