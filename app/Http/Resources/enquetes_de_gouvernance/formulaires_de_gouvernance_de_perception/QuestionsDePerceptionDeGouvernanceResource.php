<?php

namespace App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsDePerceptionDeGouvernanceResource extends JsonResource
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
            'nom' => $this->question_operationnelle->nom,
            'position' => $this->position,
            'question_operationnelle' => $this->question_operationnelle ? [
                'id' => $this->question_operationnelle->secure_id,
                'nom' => $this->question_operationnelle->nom
            ] : null,
            'formulaireDeGouvernanceId' => $this->formulaire_de_gouvernance->secure_id
        ];
    }
}
