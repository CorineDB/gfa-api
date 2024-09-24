<?php

namespace App\Http\Resources\user\auth;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
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
            "access_token" => $this['access_token'],
            "expired_at" => $this['expired_at'],
            "utilisateur" => new AuthResource($this['user'])
        ];
    }
}
