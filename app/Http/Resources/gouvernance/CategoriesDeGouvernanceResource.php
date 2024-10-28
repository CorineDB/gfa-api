<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesDeGouvernanceResource extends JsonResource
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
            'id' => $this->secure_id,
            'nom' => $this->categorieable->nom,
            'categorieDeGouvernanceParent' => $this->categorieable->categorieDeGouvernanceParent,
            'programmeId' => $this->programme->secure_id,
            'created_at' => $this->created_at,
            'questions_de_gouvernance' => $this->whenLoaded('questions_de_gouvernance', QuestionsDeGouvernanceResource::collection($this->questions_de_gouvernance))

        ];
    }
}
