<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SoumissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //'submittedBy', 'evaluationId', 'formulaireDeGouvernanceId', 'organisationId'
        return [
            'id' => $this->secure_id,
            'type' => $this->type,
            'statut' => $this->statut,
            'comite_members' => $this->comite_members,
            'commentaire' => $this->commentaire,
            'submitted_at' => Carbon::parse($this->submitted_at)->format("Y-m-d"),
            'submittedBy' => $this->authoredBy ? $this->authoredBy->secure_id : null,
            'formulaireDeGouvernanceId' => $this->formulaireDeGouvernance->secure_id,
            'evaluationId' => $this->evaluation_de_gouvernance->secure_id,
            'organisationId' => $this->organisation->secure_id,
            'programmeId' => $this->programme->secure_id,
            'reponses_de_la_collecte' => $this->reponses_de_la_collecte ? ReponsesDeLaCollecteResource::collection($this->reponses_de_la_collecte) : [],
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
