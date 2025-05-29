<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SoumissionsFactuelResource extends JsonResource
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
            'id'                    => $this->secure_id,
            'statut'                => $this->statut,
            'comite_members'        => $this->when($this->type === 'factuel', $this->comite_members),
            'submitted_at'          => Carbon::parse($this->submitted_at)->format("Y-m-d"),
            'pourcentage_evolution' => $this->pourcentage_evolution,
            'submittedBy'           => $this->authoredBy ? [
                'id'                    => $this->authoredBy->secure_id,
                'nom'                   => $this->authoredBy->nom
            ] : null,
            'formulaireDeGouvernanceId' => $this->formulaireDeGouvernance->secure_id,
            'evaluationId'              => $this->evaluation_de_gouvernance->secure_id,
            'organisationId'            => $this->organisation->secure_id,
            'programmeId'               => $this->programme->secure_id,
            'created_at'                => Carbon::parse($this->created_at)->format("Y-m-d"),
            'reponses_de_la_collecte'   => ReponsesDeLaCollecteFactuelResource::collection($this->reponses_de_la_collecte),
            //'formulaire_de_gouvernance' => new FormulairesDeGouvernanceResource($this->formulaireDeGouvernance, true, $this->id),

        ];
    }
}
