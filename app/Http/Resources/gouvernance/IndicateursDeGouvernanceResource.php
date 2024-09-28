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
            'nom'                       => $this->nom,
            'description'               => $this->description,
            'type'                      => $this->type,
            'can_have_multiple_reponse' => $this->can_have_multiple_reponse,
            'options_de_reponse'        => OptionsDeReponseResource::collection($this->options_de_reponse)
        ];
    }
}
