<?php

namespace App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesFactuelDeGouvernanceResource extends JsonResource
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
            'categorieableId' => $this->categorieable->secure_id,
            'position' => $this->position,
            'categorieDeGouvernanceId' => optional($this->categorieDeGouvernanceParent)->secure_id,

            'categories_de_gouvernance' => $this->when($this->categories_de_gouvernance->count(), CategoriesFactuelDeGouvernanceResource::collection($this->categories_de_gouvernance)),

            'questions_de_gouvernance' => $this->when($this->questions_de_gouvernance->count(), QuestionsFactuelDeGouvernanceResource::collection($this->questions_de_gouvernance))
        ];
    }
}
