<?php

namespace App\Http\Resources;

use App\Http\Resources\user\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentaireResource extends JsonResource
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
            "message" => $this->contenu,
            "auteur" => new UserResource($this->auteur),
            "date" => $this->created_at,
        ];
    }
}
