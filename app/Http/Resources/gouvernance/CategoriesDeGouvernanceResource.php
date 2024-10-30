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
            /*'categorieDeGouvernanceParent' => $this->when($this->categorieDeGouvernanceParent, function(){
                // Unset multiple relations individually
                $this->categorieDeGouvernanceParent->unsetRelation('categorieable');
                return [
                    'id' => $this->categorieDeGouvernanceParent->secure_id,
                    'nom' => $this->categorieDeGouvernanceParent->categorieable->nom,
                ];
            }),*/
            'categorieDeGouvernanceId' => optional($this->categorieDeGouvernanceParent)->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'categories_de_gouvernance' => $this->when($this->sousCategoriesDeGouvernance->count(), CategoriesDeGouvernanceResource::collection($this->sousCategoriesDeGouvernance)),

            'questions_de_gouvernance' => $this->when(!$this->sousCategoriesDeGouvernance->count(), QuestionsDeGouvernanceResource::collection($this->questions_de_gouvernance))

            //'questions_de_gouvernance' => QuestionsDeGouvernanceResource::collection($this->questions_de_gouvernance)
        ];
    }
}
