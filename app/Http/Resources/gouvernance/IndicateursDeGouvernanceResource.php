<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class IndicateursDeGouvernanceResource extends JsonResource
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
            'id'                        => $this->secure_id,
            'nom'                       => $this->when($this->nom, $this->nom),
            'description'               => $this->when($this->description, $this->description),
            'type'                      => $this->when($this->type, $this->type),
            'can_have_multiple_reponse' => $this->when($this->can_have_multiple_reponse, $this->can_have_multiple_reponse),
            'principeable' => $this->when($this->principeable, function(){
                return [

                    'id'                        => $this->principeable->secure_id,
                    'nom'                       => $this->principeable->nom,
                    'description'               => $this->principeable->description,
                ];
            }),
            'options_de_reponse'        => $this->whenLoaded('options_de_reponse', OptionDeReponseResource::collection($this->options_de_reponse))
        ];
    }
}
