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
            'id'            => $this->secure_id,
            'type'          => $this->type,
            'synthese'      => $this->synthese,
            'evaluatedAt'   => Carbon::parse($this->evaluatedAt)->format("Y-m-d"),
            'soumissionId'  => $this->soumission->secure_id,
            'programmeId'   => $this->programme->secure_id,
            'created_at'    => Carbon::parse($this->created_at)->format("Y-m-d"),
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
        ];
    }
}
