<?php

namespace App\Http\Resources;

use App\Http\Resources\user\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FichierLocalResource extends JsonResource
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
            "url" => config("app.url")."".storage_path($this->chemin),
            "auteur" => new UserResource($this->auteur),
            "extension" => pathinfo(Storage::url($this->chemin), PATHINFO_EXTENSION)
        ];
    }
}
