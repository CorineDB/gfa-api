<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FicheDeSyntheseResource extends JsonResource
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
            'id'                    => $this?->secure_id,
            'type'                  => $this?->type,
            'indice_de_gouvernance' => $this?->indice_de_gouvernance,
            'options_de_reponse'    => $this?->formulaireDeGouvernance?->options_de_reponse?->map(function($option){
                return [
                    "id"                    => $option->secure_id,
                    'libelle'               => $option->libelle,
                    'slug'                  => $option->slug,
                    'point'                 => $option->pivot->point
                ];
            }),
            'resultats'      => $this->when($this->type == 'factuel', $this->resultats),
            'synthese'      => $this->synthese,
            'evaluatedAt'   => Carbon::parse($this?->evaluatedAt)->format("Y-m-d"),
            'formulaireDeGouvernanceId' => $this?->formulaireDeGouvernance?->secure_id,
            'organisationId' => $this?->organisation?->secure_id,
            'evaluationDeGouvernanceId' => $this?->evaluation_de_gouvernance?->secure_id
            /*
            'soumission'    => [
                'id'                    => $this->soumission->secure_id,
                'type'                  => $this->soumission->type,
                'statut'                => $this->soumission->statut,
                'comite_members'        => $this->when($this->soumission->type === 'factuel',  $this->soumission->comite_members),
                'commentaire'           => $this->when($this->soumission->type === 'perception',  $this->soumission->commentaire),
                'sexe'                  => $this->when($this->soumission->type === 'perception',  $this->soumission->sexe),
                'age'                   => $this->when($this->soumission->type === 'perception',  $this->soumission->age),
                'categorieDeParticipant'=> $this->when($this->soumission->type === 'perception',  $this->soumission->categorieDeParticipant),
                'submitted_at'          => Carbon::parse($this->soumission->submitted_at)->format("Y-m-d")
            ]
            */
        ];
    }
}
