<?php

namespace App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsFactuelDeGouvernanceResource extends JsonResource
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
            'nom' => $this->indicateur_de_gouvernance->nom,
            'position' => $this->position,
            'indicateur_de_gouvernance' => $this->indicateur_de_gouvernance ? [
                'id' => $this->indicateur_de_gouvernance->secure_id,
                'nom' => $this->indicateur_de_gouvernance->nom
            ] : null
        ];
    }
}
