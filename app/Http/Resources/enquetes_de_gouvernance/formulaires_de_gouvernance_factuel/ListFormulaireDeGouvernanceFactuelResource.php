<?php

namespace App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ListFormulaireDeGouvernanceFactuelResource extends JsonResource
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
            'libelle' => $this->libelle,
            'description' => $this->description,
            'created_by' => $this->createdBy->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'options_de_reponse' => $this->options_de_reponse->map(function($option){
                return [
                    "id"                    => $option->secure_id,
                    'libelle'               => $option->libelle,
                    'slug'                  => $option->slug,
                    'point'                 => $option->pivot->point,
                    'preuveIsRequired'      => $option->pivot->preuveIsRequired,
                    'sourceIsRequired'      => $option->pivot->sourceIsRequired,
                    'descriptionIsRequired' => $option->pivot->descriptionIsRequired
                ];
            }),
            'categories_de_gouvernance' => CategoriesFactuelDeGouvernanceResource::collection($this->categories_de_gouvernance)
        ];
    }
}
