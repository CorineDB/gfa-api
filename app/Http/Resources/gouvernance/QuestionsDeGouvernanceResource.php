<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsDeGouvernanceResource extends JsonResource
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
            'type' => $this->type,
            'indicateur_de_gouvernance' => $this->indicateur_de_gouvernance ? [
                'id' => $this->indicateur_de_gouvernance->secure_id,
                'nom' => $this->indicateur_de_gouvernance->nom
            ] : null,
            'formulaireDeGouvernanceId' => $this->formulaire_de_gouvernance->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => $this->created_at
        ];
    }
}
