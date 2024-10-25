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
            'libelle' => $this->libelle,
            'description' => $this->description,
            'type' => $this->type,
            'statut' => $this->statut,
            'comite_members' => $this->comite_members,
            'commentaire' => $this->commentaire,
            'submitted_at' => Carbon::parse($this->submitted_at)->format("Y-m-d"),
            'submittedBy' => $this->authoredBy->secure_id,
            'formulaireDeGouvernanceId' => $this->formulaireDeGouvernance->secure_id,
            'evaluationId' => $this->evaluation->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => $this->created_at
        ];
    }
}
