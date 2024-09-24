<?php

namespace App\Http\Resources;

use App\Http\Resources\user\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            "texte" => $this->data['texte'],
            "module" => $this->data['module'],
            "module_id" => $this->data['id'],
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s"),
            "auteur" => new UserResource(User::find($this->data['auteurId']))
        ];
    }
}
